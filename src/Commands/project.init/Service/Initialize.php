<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInfoParser as ProjectInfoParser;
use Util\AgIndexer as AgIndexer;

class Initialize extends BaseService
{
    public function start() {
        $projectInfo = ProjectInfoParser::parse($this->options['project']);
        $dataDir = $this->config['dataRoot'].'/'.$projectInfo['projectHash'];
        $agIndexer = new AgIndexer($projectInfo['projectPath'], $projectInfo['fileExtensions'], $dataDir);
        echo "Start indexing for project {$projectInfo['projectPath']}\n";
        $agIndexer->startIndexing();
        echo "Done\n";
    }
}
