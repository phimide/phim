DROP USER IF EXISTS phimide@localhost;
CREATE USER 'phimide'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456';
GRANT ALL PRIVILEGES ON *.* TO 'phimide'@'localhost';
DROP DATABASE IF EXISTS phim_ide_db;
CREATE DATABASE phim_ide_db;
USE phim_ide_db;
CREATE TABLE phim_ide_project_index (
    project_hash varchar(32),
    index_type varchar(10), 
    index_name varchar(255),
    index_info text,
    key(project_hash),
    key(index_type),
    key(index_name)
);
