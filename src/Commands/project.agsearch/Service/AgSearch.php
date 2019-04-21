<?php
namespace Service;

use Core\BaseService as BaseService;
use Core\ProjectInfoParser as ProjectInfoParser;

class AgSearch extends BaseService
{
    public function start() {
        $projectInfo = ProjectInfoParser::parse($this->options['project']);
        $word = $this->options['word'];

        $result = "";

        $this->search(
            $word,
            $projectInfo['projectPath'],
            function($projectRealPath, $lineNumber, $file, $lineLocation, $matchInfo) use (&$result) {
                $line  = $projectRealPath."/".$file."(".$lineLocation.") ".$matchInfo;
                $result .= $lineNumber . ". " .$line."\n";
            }
        );

        $result = trim($result);

        echo $result;
    }

    public function search($word, $projectPath, $callback) {
        $projectRealPath = trim(shell_exec('cd '.$projectPath.'; pwd'));
        $cmd = "ag \"$word\" --skip-vcs-ignores --php";
        $output = trim(shell_exec($cmd));
        $lines = explode("\n", $output);

        $lineNumber = 0;
        foreach($lines as $line) {
            if (strlen($line) > 0) {
                $lineNumber ++;
                $lineSplits = explode(":", $line);
                $file = array_shift($lineSplits);
                $lineLocation = array_shift($lineSplits);
                $matchInfo = implode(":", $lineSplits);
                $callback($projectRealPath, $lineNumber, $file, $lineLocation, $matchInfo);
            }
        }
    }
}
