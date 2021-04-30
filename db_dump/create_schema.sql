CREATE DATABASE test_task;
CREATE USER 'phinx'@'%' IDENTIFIED WITH mysql_native_password BY '1';
CREATE USER 'php'@'%' IDENTIFIED WITH mysql_native_password BY '1';
GRANT ALL PRIVILEGES ON `test_task`.* TO 'dev'@'%';
GRANT ALL PRIVILEGES ON `test_task`.* TO 'phinx'@'%';
GRANT SUPER ON *.* TO 'phinx'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE TEMPORARY TABLES, LOCK TABLES, SHOW VIEW ON `test_task`.* TO 'php'@'%';
FLUSH PRIVILEGES;
