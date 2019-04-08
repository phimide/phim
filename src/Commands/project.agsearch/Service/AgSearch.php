<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInfoParser as ProjectInfoParser;

class AgSearch extends BaseService
{
    public function start() {
        $projectInfo = ProjectInfoParser::parse($this->options['project']);
        $projectPath = trim(shell_exec('cd '.$projectInfo['projectPath'].'; pwd'));
        $cmd = "ag \"{$this->options['word']}\" --skip-vcs-ignores --php";
        $output = trim(shell_exec($cmd));
        $lines = explode("\n", $output);

        $result = "";
        $lineNumber = 0;
        foreach($lines as $line) {
            $lineNumber ++;
            $lineSplits = explode(":", $line);
            $file = array_shift($lineSplits);
            $lineLocation = array_shift($lineSplits);
            $matchInfo = implode(":", $lineSplits);
            $line  = $projectPath."/".$file."(".$lineLocation.") ".$matchInfo;
            $result .= $lineNumber . ". " .$line."\n";
        }

        $result = trim($result);

        echo $result;
    }
}
