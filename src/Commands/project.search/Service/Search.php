<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\Project as Project;

class Search extends BaseService
{
    public function start() {
        $file = $this->options['file'];
        $line = $this->options['line'];

        $lines = explode("\n",trim(file_get_contents($file)));

        $contextLine = $lines[$line - 1];
        $contextPosition = $this->options['pos'];

        $project = new Project($this->options['project']);
        $result = $project->searchWord($contextLine, $contextPosition);

        $resultLength = strlen(trim($result));

        if ($resultLength > 0) {
            return $result;
        }
    }
}
