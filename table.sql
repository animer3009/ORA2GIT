-- Create table
create table SOURCE_HIST
(
  seq_id            NUMBER not null,
  owner             NVARCHAR2(100),
  name              VARCHAR2(50),
  type              VARCHAR2(50),
  sql_text          CLOB,
  ddl_time          DATE,
  status            VARCHAR2(50),
  initiator_os_user VARCHAR2(500),
  author_os_user    VARCHAR2(500),
  lag_seq_id        NUMBER,
  lead_seq_id       NUMBER,
  git_commited      NUMBER default 0
);

-- Create/Recreate indexes 
create index AUTHOR_OS_USER_INDX on SOURCE_HIST (AUTHOR_OS_USER);

create index GIT_COMMITED_INDX on SOURCE_HIST (GIT_COMMITED);

create index LAG_SEQ_ID_INDX on SOURCE_HIST (LAG_SEQ_ID);

create index LEAD_SEQ_ID_INDX on SOURCE_HIST (LEAD_SEQ_ID);

-- Create/Recreate primary, unique and foreign key constraints 
alter table SOURCE_HIST
  add constraint SEQ_ID_PK primary key (SEQ_ID)
  using index;
