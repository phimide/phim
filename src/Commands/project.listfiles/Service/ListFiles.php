<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInfoParser as ProjectInfoParser;
use Util\FileUtil;

class ListFiles extends BaseService
{
    public function start() {
        $projectInfo = ProjectInfoParser::parse($this->options['project']);
        $projectPath = $projectInfo['projectPath'];
        $fileExtensions = $projectInfo['fileExtensions'];
        $fileUtil = new FileUtil();
        echo $fileUtil->getFileListsContent($projectPath, $fileExtensions);
    }
}
