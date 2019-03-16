<?php
namespace Core;

class Config
{
    private static $config;

    public static function set($config) {
        self::$config = $config;
    }

    public static function get($key = '') {
        if ($key === '') {
            return self::$config;
        } else {
            return self::$config[$key];
        }
    }
}
