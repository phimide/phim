<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInitializer as ProjectInitializer;

class Initialize extends BaseService
{
    public function start() {
        //we are going to initialize the project
        $projectInitializer = new ProjectInitializer();
        $result = $projectInitializer->init($this->options['project']);
        echo $result;
    }
}
