<?php
namespace Service;

//use Core\BaseService;
use Core\BaseService as BaseService;
use Core\Project as Project;

class Search extends BaseService
{
    public function start() {
        $project = Project::getInstance($this->options['projecthash']);

        $file = $this->options['file'];
        $line = $this->options['line'];

        $lines = explode("\n",trim(file_get_contents($file)));

        $contextLine = $lines[$line - 1];
        $contextPosition = $this->options['pos'];

        $word = $this->getWordFromLineAndPosition($contextLine, $contextPosition);

        $result = $project->searchWordInIndex($word);

        $resultLength = strlen(trim($result));

        if ($resultLength > 0) {
            echo $result;
            return;
        }

        echo $result;
    }

    private function getWordFromLineAndPosition($contextLine, $contextPosition) {
        $leftStoppingSymbolsHash = ['>' => 1, ':' => 1, ',' => 1, ';' => 1,' ' => 1,'[' => 1,'(' => 1];
        $rightStoppingSymbolsHash = ['>' => 1,':' => 1,' ' => 1,'(' => 1,')' => 1,';' => 1,',' => 1,'{' => 1,']' => 1];

        //look to the left
        $wordLeftPos = $contextPosition;
        for ($i = $contextPosition - 1; $i >= 0; $i--) {
            $char = $contextLine[$i];
            if (isset($leftStoppingSymbolsHash[$char])) {
                break;
            }
            $wordLeftPos = $i;
        }
        //look to the right
        $contextLineLength = strlen($contextLine);
        $wordRightPos = $contextPosition + 1;
        for ($i = $contextPosition + 1; $i < $contextLineLength; $i++) {
            $char = $contextLine[$i];
            if (isset($rightStoppingSymbolsHash[$char])) {
                break;
            }
            $wordRightPos = $i;
        }

        $wordLeftPart = substr($contextLine, $wordLeftPos, $contextPosition - $wordLeftPos);
        $wordRightPart = substr($contextLine, $contextPosition, $wordRightPos - $contextPosition + 1);
        $word = $wordLeftPart.$wordRightPart;

        //also, replace \ to /
        $word = str_replace("\\", "/", $word);

        return $word;
    }
}
