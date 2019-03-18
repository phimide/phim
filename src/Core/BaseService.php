<?php
namespace Core;

class BaseService
{
    protected $options;
    protected $config;
    protected $project;

    public function __construct($options, $config) {
        $this->options = $options;
        $this->config = $config;
    }
}
