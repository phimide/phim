<?php
namespace Service;

use Core\BaseService;
use Core\Project;
use Util\FileUtil;

class ListFiles extends BaseService
{
    public function start() {
        $project = new Project($this->options['project']);
        $projectPath = $project->getProjectPath();
        $fileExtensions = $project->getFileExtensions();
        $fileUtil = new FileUtil();
        $result = $fileUtil->getFileListsContent($projectPath, $fileExtensions);
        return $result;
    }
}
