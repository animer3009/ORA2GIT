<?php

require ("conf.php");

$lock_file_name = "lock/running";
if (file_exists($lock_file_name)) {
    die("Process already running!");
}
$lock_file = fopen($lock_file_name, "w") or die("Unable create lock file!");
$txt = NULL;
fwrite($lock_file, $txt);
fclose($lock_file);

function my_die($lock_file_name, $msg) {
    unlink($lock_file_name);
    die($msg);
}

function delete_record($conn, $p_seq_id) {
    $query = "begin " . $GLOBALS['schema'] . ".delete_change_hist(:p_seq_id); end;";
    $sth = oci_parse($conn, $query);
    oci_bind_by_name($sth, "p_seq_id", $p_seq_id, -1, SQLT_INT);
    if (!$sth) {
        exit();
    }
    oci_execute($sth);
    oci_free_statement($sth);
    return 1;
}

function get_records($conn) {
    $query = "begin  :result := " . $GLOBALS['schema'] . ".change_hist_job; end;";
    $curs = oci_new_cursor($conn);
    $sth = oci_parse($conn, $query);
    oci_bind_by_name($sth, "result", $curs, -1, OCI_B_CURSOR);
    if (!$sth) {
        exit();
    }
    oci_execute($sth);
    oci_execute($curs);
    $rec_array = array();
    while ($row = oci_fetch_assoc($curs)) {
        $SQL_TEXT_CLOB = is_object($row['SQL_TEXT']) ? $row['SQL_TEXT']->load() : '';
        $row['SQL_TEXT_CLOB'] = $SQL_TEXT_CLOB;
        $rec_array[] = $row;
    }
    oci_free_statement($sth);
    oci_free_statement($curs);
    return $rec_array;
}

if (!$conn) {
    my_die($lock_file_name, "No Database Connection!");
}
$records_list = get_records($conn);
if (count($records_list) == 0) {
    my_die($lock_file_name, "No Source Code!");
}

for ($i = 0; $i < count($records_list); $i++) {

    $SEQ_ID = $records_list[$i]['SEQ_ID'];
    $OWNER = $records_list[$i]['OWNER'];
    $NAME = $records_list[$i]['NAME'];
    $TYPE = str_replace(' ', '_', $records_list[$i]['TYPE']);
    $SQL_TEXT_CLOB = $records_list[$i]['SQL_TEXT_CLOB'];
    $DDL_TIME_CHAR = $records_list[$i]['DDL_TIME_CHAR'];
    $AUTHOR_OS_USER = $records_list[$i]['AUTHOR_OS_USER'];
    $STATUS = $records_list[$i]['STATUS'];


    echo "<><><><><><><><><><><><><><><><><><><><><><><><><><><> <br /> LOOP << $SEQ_ID >> <br /> <><><><><><><><><><><><><><><><><><><><><><><><><><><> <br />";


    file_exists($OWNER) ? NULL : mkdir($OWNER, 0777);

    $source_file_name = $OWNER . '/' . $TYPE . '.' . $NAME . '.sql';
    $source_file = fopen($source_file_name, "w") or my_die($lock_file_name, "Unable to open source file! << $SEQ_ID >>");
    fwrite($source_file, $SQL_TEXT_CLOB);
    fclose($source_file);

    exec("git add '$source_file_name'", $output, $return);
    if ($return != 0) {
        my_die($lock_file_name, "[exit $return] Unable to exec git add! << $SEQ_ID >>");
    }

//echo "----------------------------------------------------- <br /> ADD <br /> ----------------------------------------------------- <br />";
//echo '<pre>';
//print_r($output);
//echo '</pre>';

    exec('git -c "user.name=' . $AUTHOR_OS_USER . '" -c "user.email=' . $AUTHOR_OS_USER . '@' .$domain. '" commit -m "' . $AUTHOR_OS_USER . ' Changed ' . $OWNER . '.' . $NAME . '\'s ' . $TYPE . ' ' . $DDL_TIME_CHAR . ' ' . '"' . ' ' . $source_file_name, $output1, $return1);

    $no_changes1 = array_search('no changes added to commit (use "git add" and/or "git commit -a")', $output1);
    $no_changes2 = array_search('nothing added to commit but untracked files present (use "git add" to track)', $output1);


    echo "----------------------------------------------------- <br /> COMMIT <br /> ----------------------------------------------------- <br />";
    echo '<pre>';
    print_r($output1);
    echo '</pre>';

    if ($return1 != 0 and ( !$no_changes1 and ! $no_changes2)) {
        my_die($lock_file_name, "[exit $return1] Unable to exec git commit! << $SEQ_ID >>");
    } else {
        delete_record($conn, $SEQ_ID);
    }
}

exec('git add index.php conf.php');
exec('git commit -m "Script files change" index.php conf.php');

exec('git push  2>&1', $output2, $return2);


echo '<br /> <br />!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! <br /> PUSH <br /> !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! <br />';
echo '<pre>';
print_r($output2);
echo '</pre>';

if ($return2 != 0) {
    my_die($lock_file_name, "[exit $return2] Unable to push!");
}

my_die($lock_file_name, "Done!!!");
