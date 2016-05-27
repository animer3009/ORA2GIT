<?php
date_default_timezone_set('Asia/Tbilisi');
ini_set('ignore_user_abort', 1);
ini_set('max_execution_time', 0);
ini_set('default_charset', 'utf-8');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$domain = 'YourDomain.com';
$schema = 'YourSchema'; //where main script is working
//try connecting to the database
$conn = oci_connect('User', 'Pass', '//DatabaseUrl:1521/Sid','UTF8');
