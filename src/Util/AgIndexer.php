<?php
namespace Util;

class AgIndexer
{
    private $projectPath;
    private $dataDir;
    private $fileExtensions;

    private $functionFinder = 'function[\s\n]+(.[a-zA-Z0-9_\-]+)[\s\n]*\(';
    private $classFinders = [
        'class[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9_,\-\s\n]*{',
        'interface[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{',
        'trait[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{'
    ];

    public function __construct($projectPath, $fileExtensions, $dataDir) {
        $this->projectPath = $projectPath;
        $this->fileExtensions = $fileExtensions;
        $this->dataDir = $dataDir;
    }

    public function startIndexing() {
        //clear old indexes
        $this->clearIndexs();

        //start indexing
        $projectRealPath = trim(shell_exec('cd '.$this->projectPath.'; pwd'));
        $word = $this->classFinders[0];
        $cmd = "cd {$projectRealPath};ag \"$word\" --skip-vcs-ignores --php";
        $output = shell_exec($cmd);
        $lines = explode("\n", trim($output));
        $lineNumber = 0;
        foreach($lines as $line) {
            $lineSplits = explode(":", $line);
            $startingToken = substr($lineSplits[2], 0, 5);
            if ($startingToken === 'class') {
                $lineNumber ++;
                $file = array_shift($lineSplits);
                $lineLocation = array_shift($lineSplits);
                $matchInfo = implode(":", $lineSplits);
                $matchInfoSplits = explode(" ", $matchInfo);
                $classSplits = explode(" ", $matchInfoSplits[1]);
                $className = str_replace(['{'],'', $classSplits[0]);
                $matchContent  = $projectRealPath."/".$file."(".$lineLocation.") ".$matchInfo;
                $this->saveSingleIndex('class', $className, $matchContent);
            }
        }

        $result = "done";
    }

    public function clearIndexs() {
        $cmd = "find {$this->dataDir} -type f -name \"*.index\" -delete";
        shell_exec($cmd);
    }

    /**
     * save a single index item
     */
    public function saveSingleIndex($indexType, $indexName, $indexContent) {
        if (strlen($indexName) > 0 &&
            $indexName[0] !== '*'
        )  {
            $indexFilePath = $this->dataDir.'/'.$indexType.'.'.$indexName.'.index';
            file_put_contents($indexFilePath, $indexContent, \FILE_APPEND);
        }
    }
}
