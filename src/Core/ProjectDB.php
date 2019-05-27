<?php
namespace Core;

class ProjectDB
{
    public function __construct($dbConfig) {
        $dbh = new \PDO("{$dbConfig['type']}:host={$dbConfig['host']};dbname={$dbConfig['name']}", $dbConfig['user'], $dbConfig['pass']);
        var_dump($dbh);
    }
}
