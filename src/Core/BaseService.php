<?php
namespace Core;

use Core\ProjectDB;

class BaseService
{
    protected $options;
    protected $config;

    public function __construct($options, $config) {
        $this->options = $options;
        $this->config = $config;
    }
}
