<?php
namespace Service;

use \Core\BaseService as BaseService;

class Search extends BaseService
{
    public function start() {
        $project = $this->getProject();
        $path = trim(shell_exec('pwd '.$project->getPath()));
        $extendsions = $project->getFileExtensions();

        $file = $this->options['file'];
        $line = $this->options['line'];

        $lines = explode("\n",trim(file_get_contents($file)));

        $contextLine = $lines[$line - 1];
        $contextPosition = $this->options['pos'];

        //first, try to see if this is a possible class
        $contextLine = str_replace('::',' ', $contextLine);

        $leftPart = substr($contextLine, 0, $contextPosition - 1);
        $rightPart = substr($contextLine, $contextPosition - 1, strlen($contextLine));

        $leftTokens = explode(" ", $leftPart);
        $rightTokens = explode(" ", $rightPart);

        $wordLeftPart = array_pop($leftTokens);
        $wordRightPart = array_shift($rightTokens);

        $word = $wordLeftPart.$wordRightPart;

        //now remove all "(,),;"
        $word = str_replace(['(',')',';'], "",$word);
        //replace \ to /
        $word = str_replace("\\","/", $word);

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
                foreach($fileInfos as $fileInfo) {
                    if (strpos($fileInfo[0], $word) !== FALSE) {
                        $result .= "{$fileInfo[0]}({$fileInfo[1]})\n";
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
        $wordLeftPos = $contextPosition - 1;
        for ($i = $wordLeftPos; $i >= 0; $i--) {
            if (in_array($contextLine[$i], ['>',':',' '])) {
                $wordLeftPos = $i + 1;
                break;
            }
        }
        //look to the right
        $contextLineLength = strlen($contextLine);
        $wordRightPos = $contextPosition;
        for ($i = $wordRightPos; $i < $contextLineLength; $i++) {
            if (in_array($contextLine[$i], ['>',':',' ','(',')'])) {
                $wordRightPos = $i - 1;
                break;
            }
        }
        $word = substr($contextLine, $wordLeftPos, $contextPosition - $wordLeftPos)
            .substr($contextLine, $contextPosition, $wordRightPos - $contextPosition + 1);

        $projectIndex = $project->getIndex();
        $functionsIndex = $projectIndex['functions'];
        if (isset($functionsIndex[$word])) {
            $fileInfos = $functionsIndex[$word];
            foreach($fileInfos as $fileInfo) {
                $result .= "{$fileInfo[0]}({$fileInfo[1]})\n";
            }
        }
        
        echo $result;
    }
}
