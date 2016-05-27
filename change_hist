CREATE OR REPLACE TRIGGER change_hist
  BEFORE DDL ON DATABASE
DECLARE
  v_cnt            number;
  V_SQLERRM        nvarchar2(4000);
  v_sql_text       source_hist.sql_text%TYPE;
  v_os_user        source_hist.initiator_os_user%TYPE := SYS_CONTEXT('USERENV',
                                                                     'OS_USER');
BEGIN

  if v_os_user <> 'oracle' then
    begin
      select sys.get_ddl_source(decode(ora_dict_obj_type,
                                       'DATABASE LINK',
                                       'DB_LINK',
                                       'JOB',
                                       'PROCOBJ',
                                       'PACKAGE',
                                       'PACKAGE_SPEC',
                                       'PACKAGE BODY',
                                       'PACKAGE_BODY',
                                       'TYPE',
                                       'TYPE_SPEC',
                                       'TYPE BODY',
                                       'TYPE_BODY',
                                       'MATERIALIZED VIEW',
                                       'MATERIALIZED_VIEW',
                                       ora_dict_obj_type),
                                ora_dict_obj_name,
                                ora_dict_obj_owner)
        into v_sql_text
        from dual;
    exception
      when others then
        V_SQLERRM := SQLERRM;
    end;
  
    if V_SQLERRM is not null then
      select count(1)
        into v_cnt
        from DBA_OBJECTS g
       where g.OWNER = ora_dict_obj_owner
         and g.OBJECT_NAME = ora_dict_obj_name
         and g.OBJECT_TYPE = ora_dict_obj_type;
    
      if v_cnt <> 0 then
        raise_application_error(-20000, V_SQLERRM);
      end if;
    else
      insert into source_hist
        select SOURCE_HIST_SEQ.NEXTVAL,
               f.OWNER,
               f.OBJECT_NAME,
               f.OBJECT_TYPE,
               v_sql_text,
               f.LAST_DDL_TIME,
               f.status,
               v_os_user,
               NULL,
               NULL,
               NULL,
               0
          from dba_objects f
         where f.OBJECT_NAME = ora_dict_obj_name
           and f.OBJECT_TYPE = ora_dict_obj_type
           and f.OWNER = ora_dict_obj_owner;
    end if;
  end if;

EXCEPTION
  WHEN OTHERS THEN
    raise_application_error(-20001, SQLERRM);
    null;
END;
