<?php
namespace Core;

class Options 
{
    private static $options;

    public static function set($options) {
        self::$options= $options;
    }

    public static function get($key = '') {
        if ($key === '') {
            return self::$options;
        } else {
            return self::$options[$key];
        }
    }
}
