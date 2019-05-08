<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInfoParser as ProjectInfoParser;

class ListFiles extends BaseService
{
    public function start() {
        $projectInfo = ProjectInfoParser::parse($this->options['project']);
        $projectPath = $projectInfo['projectPath'];
        $fileExtensions = $projectInfo['fileExtensions'];
        $patterns = [];
        foreach($fileExtensions as $extension) {
            $patterns[] = "-name \"*.$extension\"";
        }
        $patternsStr = implode(" -o ", $patterns);
        $cmd = "find -H $projectPath -type f $patternsStr";
        print shell_exec($cmd);
    }
}
