<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectHash as ProjectHash;

class GetHash extends BaseService
{
    public function start() {
        $result = ProjectHash::get($this->options['project']);
        echo $result;
    }
}
