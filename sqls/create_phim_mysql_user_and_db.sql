DROP USER IF EXISTS phimide@localhost;
CREATE USER 'phimide'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456';
GRANT ALL PRIVILEGES ON *.* TO 'phimide'@'localhost';
DROP DATABASE IF EXISTS phim_ide_db;
CREATE DATABASE phim_ide_db;
