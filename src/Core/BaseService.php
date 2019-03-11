<?php
namespace Core;

class BaseService
{
    protected $options;
    protected $project;

    public function __construct($options) {
        $this->options = $options;
    }

    public function setProject($project) {
        $this->project = $project;
    }

    public function getProject() {
        return $this->project;
    }
}
