<?php
namespace Service;

use Core\BaseService;
use Core\Project;

class Initialize extends BaseService
{
    public function start() {
        //we are going to initialize the project
        $project = new Project($this->options['project']);
        $project->createIndex();
    }
}
