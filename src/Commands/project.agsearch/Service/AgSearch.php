<?php
namespace Service;

use Core\BaseService;
use Core\Project;

class AgSearch extends BaseService
{
    public function start() {
        $project = new Project($this->options['project']);
        $projectPath = $project->getProjectPath();
        $cmd = "cd $projectPath; ag -w \"{$this->options['word']}\" --skip-vcs-ignores";
        $output = trim(shell_exec($cmd));
        $fileInfos = explode("\n", $output);

        $result = $project->getResultFromFileInfos($fileInfos);

        return $result;
    }
}
