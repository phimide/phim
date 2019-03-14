<?php
namespace Core;

class InMemoryDataStore
{
    private static $instance;
    private $redis;

    public static function getInstance($config) {
        if (!isset(self::$instance)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function __construct($config) {
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
    }

    public function set($key, $val) {
        $this->redis->set($key, $val);
    }

    public function get($key) {
        $result = $this->redis->get($key);
        return $result;
    }
}
