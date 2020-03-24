<?php
namespace Core;

class ProjectDB
{
    private $dbh;

    public function __construct() {
        $config = require (__DIR__."/../../config/config.php");
        $dbConfig = $config['db'];
        $this->dbh = new \PDO("{$dbConfig['type']}:host={$dbConfig['host']};dbname={$dbConfig['name']}", $dbConfig['user'], $dbConfig['pass']);
    }

    public function getRowsFromSQL($sql) {
        $res = $this->dbh->query($sql, \PDO::FETCH_ASSOC);
        $rows = [];
        foreach($res as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function doSQL($sql) {
        $count = $this->dbh->exec($sql);
        return $count;
    }
}
