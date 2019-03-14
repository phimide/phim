<?php
namespace Core;

class BaseService
{
    use LoggableTrait; 

    protected $options;
    protected $config;
    protected $project;

    public function __construct($options, $config) {
        $this->options = $options;
        $this->config = $config;
    }

    public function setProject($project) {
        $this->project = $project;
    }

    public function getProject() {
        return $this->project;
    }
}
