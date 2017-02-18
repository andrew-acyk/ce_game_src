create database for ce_schema
ubuntu> mysql -u root -p
mysql> create database ce_schema;
mysql> create user ce_user@localhost identified by '4Apri!Fu!!';
mysql> grant all privileges on ce_schema.* to ce_user@localhost;
-- Use new privileges now
mysql> flush privileges;
mysql> exit;

drop user sooheel;

CREATE user 'sooheel'@'%' IDENTIFIED BY  'changeme';

grant select, insert, update, delete, create, drop on ce_schema.* to 'sooheel'@'%';

flush privileges;

drop user ce_user;

CREATE user 'ce_user'@'%' IDENTIFIED BY  '4Apri!Fu!!';

grant select, insert, update, delete, create, drop on ce_schema.* to 'ce_user'@'%';

flush privileges;

set password for ce_user = password('4Apri!Fu!!');

drop user andrewk;

CREATE user 'andrewk'@'localhost' IDENTIFIED BY  '4Apri!Fu!!';

grant select, insert, update, delete, create, drop on ce_schema.* to 'andrewk'@'%';

flush privileges;

set password for andrewk = password('4Apri!Fu!!');