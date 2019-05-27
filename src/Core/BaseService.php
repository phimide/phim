<?php
namespace Core;

use Core\ProjectDB;

class BaseService
{
    protected $options;
    protected $config;
    protected $projectDB;

    public function __construct($options, $config) {
        $this->options = $options;
        $this->config = $config;
        $this->projectDB = new ProjectDB($config['db']);
    }
}
