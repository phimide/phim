<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInfoParser as ProjectInfoParser;

class GetHash extends BaseService
{
    public function start() {
        $projectInfo = ProjectInfoParser::parse($this->options['project']);
        $result = $projectInfo['projectHash'];
        return $result;
    }
}
