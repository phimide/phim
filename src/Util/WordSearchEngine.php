<?php
namespace Util;

class WordSearchEngine
{
    private $projectHash;
    private $dataRoot;

    public function __construct($projectHash, $dataRoot) {
        $this->projectHash = $projectHash;
        $this->dataRoot = $dataRoot;
    }

    public function doSearch($file, $contextLine, $contextPosition) {
        $word = $this->getWordFromLineAndPosition($contextLine, $contextPosition); 
        $dataDir = $this->dataRoot.'/'.$this->projectHash;
        $result = [];
    }

    private function getWordFromLineAndPosition($contextLine, $contextPosition) {
        $leftStoppingSymbolsHash = [',' => 1, ';' => 1,' ' => 1,'[' => 1,'(' => 1,'\'' => 1,'+' => 1];
        $rightStoppingSymbolsHash = [' ' => 1,'(' => 1,')' => 1,';' => 1,',' => 1,'{' => 1,']' => 1, '\'' => 1];

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
