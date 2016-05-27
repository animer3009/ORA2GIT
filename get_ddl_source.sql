create or replace function get_ddl_source(p_object_type VARCHAR2,
                                          p_name        VARCHAR2,
                                          p_schema      VARCHAR2)
  return clob is

begin
  return DBMS_METADATA.get_ddl(p_object_type, p_name, p_schema);
end get_ddl_source;
