<?php
namespace Service;

//use Core\BaseService;
use \Core\BaseService as BaseService;

class Search extends BaseService
{
    public function start() {
        $project = $this->getProject();

        $file = $this->options['file'];
        $line = $this->options['line'];

        $lines = explode("\n",trim(file_get_contents($file)));

        $contextLine = $lines[$line - 1];
        $contextPosition = $this->options['pos'];

        $word = $this->getWordFromLineAndPosition($contextLine, $contextPosition);

        $result = '';
        if (strlen($word) > 0) {
            $wordComps = explode("/", $word);
            $wordPop = array_pop($wordComps);
            $projectIndex = $project->getIndex();
            //first search the class index
            $classesIndex = $projectIndex['classes'];
            if (isset($classesIndex[$wordPop])) {
                //now fine tune the result, no need to show unrelated files
                $fileInfos = $classesIndex[$wordPop];
                $lineNum = 0;
                foreach($fileInfos as $fileInfo) {
                    if (strpos($fileInfo[0], $word) !== FALSE) {
                        $lineNum ++;
                        $result .= "$lineNum. {$fileInfo[0]}({$fileInfo[1]})\n";
                    }
                }
                //if we did not find it, we search for the pure word
                if ($lineNum === 0) {
                    foreach($fileInfos as $fileInfo) {
                        if (strpos($fileInfo[0], $wordPop) !== FALSE) {
                            $lineNum ++;
                            $result .= "$lineNum. {$fileInfo[0]}({$fileInfo[1]})\n";
                        }
                    }
                }
            }
        }

        $resultLength = strlen(trim($result));

        if ($resultLength > 0) {
            echo $result;
            return;
        }

        //now there is no match on the classes, try to find the functions
        //look to the left

        $projectIndex = $project->getIndex();
        $functionsIndex = $projectIndex['functions'];
        if (isset($functionsIndex[$word])) {
            $fileInfos = $functionsIndex[$word];
            $lineNum = 0;
            foreach($fileInfos as $fileInfo) {
                $lineNum ++;
                $result .= "$lineNum. {$fileInfo[0]}({$fileInfo[1]})\n";
            }
        }

        echo $result;
    }

    private function getWordFromLineAndPosition($contextLine, $contextPosition) {
        $leftStoppingSymbolsHash = ['>' => 1, ':' => 1, ',' => 1, ';' => 1,' ' => 1];
        $rightStoppingSymbolsHash = ['>' => 1,':' => 1,' ' => 1,'(' => 1,')' => 1,';' => 1,',' => 1,'{' => 1];

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
