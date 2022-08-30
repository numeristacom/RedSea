create table upsert_test_ut (ut_id integer not null primary key auto_increment,
 one integer not null,
 two integer null,
 three text,
 four decimal(5,3),
 five numeric(5,3),
 six fixed(5,3),
 seven real(5,3),
 eight double precision(5,3),
 nine bit(8),
 ten date,
 eleven time,
 twelve datetime);

-- Simple SQL text & number test
create table upsert_test2 (ut_id integer not null primary key auto_increment,
 one integer not null,
 two integer null,
 three text not null,
 four text null,
 five datetime);

 create table ut3 (one integer not null,
 two integer null,
 three text not null,
 four text null,
 five datetime);

 