create or replace function change_hist_job return sys_refcursor is
  result           sys_refcursor;
  v_author_os_user SOURCE_HIST.AUTHOR_OS_USER%TYPE;
begin

  for r in (select *
              from (select h.*,
                           LAG(h.initiator_os_user, 1) OVER(PARTITION BY h.owner, h.name, h.type ORDER BY h.seq_id asc) AS s_author_os_user,
                           LAG(h.seq_id, 1) OVER(PARTITION BY h.owner, h.name, h.type ORDER BY h.seq_id asc) AS s_LAG_SEQ_ID,
                           LEAD(h.seq_id, 1) OVER(PARTITION BY h.owner, h.name, h.type ORDER BY h.seq_id asc) AS s_LEAD_SEQ_ID
                      from SOURCE_HIST h) g
             where g.lag_seq_id is null
                or g.lead_seq_id is null
             order by g.seq_id asc) loop
  
    if r.lag_seq_id is null and r.s_lag_seq_id is not null then
      v_author_os_user := nvl(r.s_author_os_user, 'xuser');
      update SOURCE_HIST g
         set g.lag_seq_id     = r.s_lag_seq_id,
             g.author_os_user = v_author_os_user
       where g.seq_id = r.seq_id;
    end if;
  
    if r.lead_seq_id is null and r.s_lead_seq_id is not null then
      update SOURCE_HIST g
         set g.lead_seq_id = r.s_lead_seq_id
       where g.seq_id = r.seq_id;
    end if;
  
    if r.lag_seq_id is null and r.lead_seq_id is not null then
      delete from SOURCE_HIST j where j.seq_id = r.seq_id;
    end if;
  
  end loop;

  commit;

  open result for
    select b.*,
           to_char(b.ddl_time, 'DD/MM/YYYY hh24:mi:ss') as ddl_time_char
      from SOURCE_HIST b
     where b.seq_id in
           (select w.seq_id
              from (select min(h.seq_id) as seq_id, h.owner, h.name, h.type
                      from SOURCE_HIST h
                     where h.lag_seq_id is not null
                       and h.git_commited = 0
                     group by h.owner, h.name, h.type) w);

  return result;

exception
  when others then
    open result for
      select * from dual where 1 = 2;
    return result;
end change_hist_job;
