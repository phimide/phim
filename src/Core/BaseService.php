<?php
namespace Core;

class BaseService
{
    protected $options;
    protected $project;

    public function __construct($options, $config) {
        $this->options = $options;
    }
}
