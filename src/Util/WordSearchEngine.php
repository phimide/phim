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

        $wordInfo = $this->getWordFromLineAndPosition($contextLine, $contextPosition);

        $word = $wordInfo['word'];
        $wordSplits = explode("::", $word);
        $wordSplitsCount = count($wordSplits);
        if ($wordSplitsCount === 2) {
            //pattern: class::member
            $classPath = $wordSplits[0];
            $classPathSplits = explode("/", $classPath);
            $className = array_pop($classPathSplits);
            $classIndex = $this->dataDir."/class.$className.index";
            if (file_exists($classIndex)) {
                $fileInfos = explode("\n",trim(file_get_contents($classIndex)));
                $patterns = [$classPath, $className];
                foreach($patterns as $pattern) {
                    foreach($fileInfos as $fileInfo) {
                        $file = explode(":", $fileInfo)[0];
                        if (strpos($file, $pattern) !== FALSE) {
                            $possibleFileInfos[$file] = $fileInfo;
                        }
                    }
                    if (count($possibleFileInfos) > 0) {
                        break;
                    }
                }
            }

            $functionName = $wordSplits[1];
            $functionIndex = $this->dataDir."/function.$functionName.index";
            if (file_exists($functionIndex)) {
                $fileInfos = explode("\n",trim(file_get_contents($functionIndex)));
                foreach($fileInfos as $fileInfo) {
                    $file = explode(":", $fileInfo)[0];
                    if (isset($possibleFileInfos[$file])) {
                        $possibleFileInfos[$file] = $fileInfo;
                    }
                }
            }
        } else if ($wordSplitsCount === 1) {
            //pattern: class|trait|function
            $classPath = $wordSplits[0];
            $classPathSplits = explode("/", $classPath);
            $className = array_pop($classPathSplits);
            $classIndex = $this->dataDir."/class.$className.index";
            if (file_exists($classIndex)) {
                $fileInfos = explode("\n",trim(file_get_contents($classIndex)));
                $patterns = [$classPath, $className];
                foreach($patterns as $pattern) {
                    foreach($fileInfos as $fileInfo) {
                        $file = explode(":", $fileInfo)[0];
                        if (strpos($file, $pattern) !== FALSE) {
                            $possibleFileInfos[$file] = $fileInfo;
                        }
                    }
                    if (count($possibleFileInfos) > 0) {
                        break;
                    }
                }
            }
        }

        $result = $this->getResultFromFileInfos($possibleFileInfos);

        return $result;
    }

    private function getWordFromLineAndPosition($contextLine, $contextPosition) {
        $leftStoppingSymbolsHash = [',' => 1, ';' => 1,' ' => 1,'[' => 1,'\'' => 1,'+' => 1,')' => 1, '(' => 1];
        $rightStoppingSymbolsHash = [' ' => 1,';' => 1,',' => 1,'{' => 1,']' => 1, '\'' => 1, ')' => 1, '(' => 1];

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

        $result = [
            'word' => $word,
            'leftPos' => $wordLeftPos,
            'rightPos' => $wordRightPos,
        ];
        return $result;
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
