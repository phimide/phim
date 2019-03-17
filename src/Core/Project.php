<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

class Project {
    private static $instance;
    private $projectHash;
    private $projectIndexFile;

    public static function getInstance($projectHash) {
        if (!isset(self::$instance)) {
            self::$instance = new self($projectHash);
        }
        return self::$instance;
    }

    public function __construct($projectHash) {
        $this->projectHash = $projectHash;
    }

    public function getProjectHash() {
        return $this->projectHash;
    }

    public function getFileExtensions() {
        return $this->fileExtensions;
    }

    public function searchWordInIndex($word) {
        $result = "";
        $dataDir = Config::get('dataRoot').'/'.$this->projectHash;
        if (strlen($word) > 0) {
            $wordComps = explode("/", $word);
            $wordPop = array_pop($wordComps);
            //first search the class index
            $classIndex = $dataDir."/class.$wordPop.json";
            if (file_exists($classIndex)) {
                //now fine tune the result, no need to show unrelated files
                $fileInfos = json_decode(file_get_contents($classIndex));
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

            if (strlen($result) === 0) {
                //not exists in class index, search for function instead
                $functionIndex = $dataDir."/function.$wordPop.json";
                if (file_exists($functionIndex)) {
                    $fileInfos = json_decode(file_get_contents($functionIndex));
                    $lineNum = 0;
                    foreach($fileInfos as $fileInfo) {
                        $lineNum ++;
                        $result .= "$lineNum. {$fileInfo[0]}({$fileInfo[1]})\n";
                    }
                }
            }
        }
        return $result;
    }

    /**
     * save the project index
     */
    public function saveIndex($indexData) {
        //file_put_contents($this->projectIndexFile, json_encode($indexData));
        $dataDir = Config::get('dataRoot').'/'.$this->projectHash;
        $classIndexData = $indexData['classes'];
        foreach($indexData['classes'] as $class => $fileInfo) {
            if ($class[0] !== '*') { //do not know why, there is a class starts with *, we should exclude it
                $indexFileBasename = "class.$class.json";
                $indexFile = $dataDir.'/'.$indexFileBasename;
                file_put_contents($indexFile, json_encode($fileInfo));
            }
        }
        $functionsIndexData = $indexData['functions'];
        foreach($indexData['functions'] as $function => $fileInfo) {
            $indexFileBasename = "function.$function.json";
            $indexFile = $dataDir.'/'.$indexFileBasename;
            file_put_contents($indexFile, json_encode($fileInfo));
        }
    }
}
