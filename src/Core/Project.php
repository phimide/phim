<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

class Project {
    private $projectHash;
    private $dataRoot;
    private $dataDir;

    public function __construct($projectHash, $dataRoot) {
        $this->projectHash = $projectHash;
        $this->dataRoot = $dataRoot;
        $this->dataDir = $this->dataRoot.'/'.$this->projectHash;
    }

    public function getProjectHash() {
        return $this->projectHash;
    }

    public function getFileExtensions() {
        return $this->fileExtensions;
    }

    public function searchWordInIndex($word) {
        $result = "";
        $dataDir = $this->dataRoot.'/'.$this->projectHash;
        if (strlen($word) > 0) {
            $wordComps = explode("/", $word);
            $wordPop = array_pop($wordComps);
            //first search the class index
            $classIndex = $dataDir."/class.$wordPop.index";
            if (file_exists($classIndex)) {
                //now fine tune the result, no need to show unrelated files
                $fileInfos = explode("\n",trim(file_get_contents($classIndex)));
                $lineNum = 0;
                foreach($fileInfos as $fileInfo) {
                    $comps = explode(":", $fileInfo);
                    $filePath = $comps[0];
                    $line = $comps[1];
                    if (strpos($filePath, $word) !== FALSE) {
                        $lineNum ++;
                        $result .= "$lineNum. {$filePath}({$line})\n";
                    }
                }
                //if we did not find it, we search for the pure word
                if ($lineNum === 0) {
                    foreach($fileInfos as $fileInfo) {
                        $comps = explode(":", $fileInfo);
                        $filePath = $comps[0];
                        $line = $comps[1];
                        if (strpos($filePath, $wordPop) !== FALSE) {
                            $lineNum ++;
                            $result .= "$lineNum. {$filePath}({$line})\n";
                        }
                    }
                }
            }

            if (strlen($result) === 0) {
                //not exists in class index, search for function instead
                $functionIndex = $dataDir."/function.$wordPop.index";
                if (file_exists($functionIndex)) {
                    $fileInfos = explode("\n",trim(file_get_contents($functionIndex)));
                    $lineNum = 0;
                    foreach($fileInfos as $fileInfo) {
                        $comps = explode(":", $fileInfo);
                        $filePath = $comps[0];
                        $line = $comps[1];
                        $lineNum ++;
                        $result .= "$lineNum. {$filePath}({$line})\n";
                    }
                }
            }
        }
        return $result;
    }

    public function clearIndexs() {
        shell_exec("rm -rf {$this->dataDir}; mkdir {$this->dataDir}");
    }

    /**
     * save a single index item
     */
    public function saveSingleIndex($indexType, $indexName, $indexContent) {
        if (strlen($indexName) > 0 &&
            $indexName[0] !== '*'
        )  {
            $indexFilePath = $this->dataDir.'/'.$indexType.'.'.$indexName.'.index';
            file_put_contents($indexFilePath, $indexContent, \FILE_APPEND);
        }
    }
}
