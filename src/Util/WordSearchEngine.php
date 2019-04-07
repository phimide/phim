<?php
namespace Util;

class WordSearchEngine
{
    private $projectHash;
    private $dataRoot;
    private $dataDir;

    public function __construct($projectHash, $dataRoot) {
        $this->projectHash = $projectHash;
        $this->dataRoot = $dataRoot;
        $this->dataDir = $this->dataRoot.'/'.$this->projectHash;
    }

    public function doSearch($file, $contextLine, $contextPosition) {
        $possibleFileInfos = [];

        $word = $this->getWordFromLineAndPosition($contextLine, $contextPosition); 

        //pattern: class::method
        $wordSplits = explode("::", $word);
        $wordSplitsCount = count($wordSplits);
        if ($wordSplitsCount === 2) {
            $classPath = $wordSplits[0]; 
            $classPathSplits = explode("/", $classPath);
            $className = array_pop($classPathSplits);
            $classIndex = $this->dataDir."/class.$className.index";
            if (file_exists($classIndex)) {
                $fileInfos = explode("\n",trim(file_get_contents($classIndex)));
                foreach($fileInfos as $fileInfo) {
                    $file = explode(":", $fileInfo)[0];
                    if (strpos($file, $classPath) !== FALSE) {
                        $possibleFileInfos[] = $fileInfo;
                    }
                }
            }
        }

        $result = $this->getResultFromFileInfos($possibleFileInfos);

        return $result;
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

    private function getResultFromFileInfos($possibleFileInfos) {
        $result = "";
        $lineNum = 0;
        foreach($possibleFileInfos as $fileInfo) {
            $lineNum ++;
            $comps = explode(":", $fileInfo);
            $filePath = $comps[0];
            $line = $comps[1];
            $result .= "$lineNum. {$filePath}({$line})\n";
        }
        return $result;
    }
}
