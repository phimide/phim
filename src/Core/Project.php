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
    private $serializeHanlder;
    private $unserializeHandler;

    public static function getInstance($projectHash) {
        if (!isset(self::$instance)) {
            self::$instance = new self($projectHash);
        }
        return self::$instance;
    }

    public function __construct($projectHash) {
        $this->projectHash = $projectHash;
        $this->serializeHandler = Config::get('serialize_handler');
        $this->unserializeHandler = Config::get('unserialize_handler');
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
            $classIndex = $dataDir."/class.$wordPop.index";
            if (file_exists($classIndex)) {
                //now fine tune the result, no need to show unrelated files
                $fileInfos = ($this->unserializeHandler)(file_get_contents($classIndex));
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
                $functionIndex = $dataDir."/function.$wordPop.index";
                if (file_exists($functionIndex)) {
                    $fileInfos = ($this->unserializeHandler)(file_get_contents($functionIndex));
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
        $dataDir = Config::get('dataRoot').'/'.$this->projectHash;
        shell_exec("rm -rf $dataDir; mkdir $dataDir");
        $classIndexData = $indexData['classes'];
        foreach($indexData['classes'] as $class => $fileInfo) {
            if (@$class[0] !== '*') { //do not know why, there is a class starts with *, we should exclude it
                $indexFileBasename = "class.$class.index";
                $indexFile = $dataDir.'/'.$indexFileBasename;
                file_put_contents($indexFile, ($this->serializeHandler)($fileInfo));
            }
        }
        $functionsIndexData = $indexData['functions'];
        foreach($indexData['functions'] as $function => $fileInfo) {
            $indexFileBasename = "function.$function.index";
            $indexFile = $dataDir.'/'.$indexFileBasename;
            file_put_contents($indexFile, ($this->serializeHandler)($fileInfo));
        }
    }
}
