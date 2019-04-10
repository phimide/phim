<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\Project as Project;
use Util\WordSearchEngine as WordSearchEngine;

class Search extends BaseService
{
    public function start() {
        $project = new Project($this->options['projecthash'], $this->config['dataRoot']);

        $file = $this->options['file'];
        $line = $this->options['line'];

        $lines = explode("\n",trim(file_get_contents($file)));

        $contextLine = $lines[$line - 1];
        $contextPosition = $this->options['pos'];

        $searchEngine = new WordSearchEngine($this->options['projecthash'], $this->config['dataRoot']);
        $result = $searchEngine->doSearch($file, $contextLine, $contextPosition);
        
        $resultLength = strlen(trim($result));

        if ($resultLength > 0) {
            echo $result;
            return;
        }
    } 
}
