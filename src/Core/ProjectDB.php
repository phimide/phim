<?php
namespace Core;

class ProjectDB
{
    private $dbh;

    public function __construct($dbConfig) {
        $this->dbh = new \PDO("{$dbConfig['type']}:host={$dbConfig['host']};dbname={$dbConfig['name']}", $dbConfig['user'], $dbConfig['pass']);
    }

    public function getRowsFromSQL($sql) {
    }

    public function doSQL($sql) {
    }
}
