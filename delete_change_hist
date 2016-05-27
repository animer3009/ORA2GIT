create or replace procedure delete_change_hist(p_seq_id number) is
begin
  update SOURCE_HIST j set j.git_commited = 1 where j.seq_id = p_seq_id;
  commit;

  delete from SOURCE_HIST g
   where g.lead_seq_id is not null
     and g.git_commited = 1;
  commit;
end delete_change_hist;
