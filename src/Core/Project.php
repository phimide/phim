<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

class Project {
    private $projectHash;
    private $projectPath;
    private $fileExtensions;
    private $dataRoot;
    private $dataDir;
    private $indexPath;

    public function __construct($rawProjectInfo) {
        $projectInfo = $this->parse($rawProjectInfo);
        $this->projectHash = $projectInfo['projectHash'];
        $this->projectPath = $projectInfo['projectPath'];
        $this->fileExtensions = $projectInfo['fileExtensions'];
    }

    public function parse($rawProjectInfo) {
        $projectDetails = explode("|", $rawProjectInfo);
        $path = trim($projectDetails[0]);
        $fileExtensionsStr = trim($projectDetails[1]);
        $fileExtensions = explode(",", $fileExtensionsStr);

        $fileExtensionsCount = count($fileExtensions);
        for ($i = 0; $i < $fileExtensionsCount; $i++) {
            $fileExtensions[$i] = trim($fileExtensions[$i]);
        }
        $fileExtensionsStr = implode(",",$fileExtensions);
        $projectHash = md5($path."|".$fileExtensionsStr);
        $result = [
            'projectPath' => $path,
            'projectHash' => $projectHash,
            'fileExtensions' => $fileExtensions
        ];
        return $result;
    }

    public function getProjectHash() {
        return $this->projectHash;
    }

    public function getFileExtensions() {
        return $this->fileExtensions;
    }

    public function searchWordInIndex($word) {
        $indexMap = $this->getIndexMap();
        print_r($indexMap);exit;
        if (strlen($word) > 0) {
            $wordCompsByTwoColons =  explode("::", $word);

            $className = "";
            $functionName = "";

            $wordCompsByForwardSlashes = explode("/", $wordCompsByTwoColons[0]);
            if (count($wordCompsByTwoColons) > 1) { //this is like class::member
                $className = array_pop($wordCompsByForwardSlashes);
                $functionName = $wordCompsByTwoColons[1];
            } else {
                //this is possibly be a class
                $className = array_pop($wordCompsByForwardSlashes);
            }

            $possibleFileInfos = [];
            if (strlen($className) > 0) {
                //search the class index
                $classIndex = $dataDir."/class.$className.index";
                $classPath = $wordCompsByTwoColons[0];
                $classFiles = [];
                $classFilesBySimilarity = [];
                if (file_exists($classIndex)) {
                    $fileInfos = explode("\n",trim(file_get_contents($classIndex)));
                    $possibleFileInfos = $fileInfos;
                    foreach($fileInfos as $fileInfo) {
                        $file = explode(":", $fileInfo)[0];
                        $classFiles[] = $file;
                        $similarity = \similar_text($file, $classPath);
                        $fileInfosBySimilarity[$similarity] = $fileInfo;
                    }
                    if (count($fileInfosBySimilarity) > 0) {
                        krsort($fileInfosBySimilarity);
                        $possibleFileInfos = array_values($fileInfosBySimilarity);
                    }
                }
            }

            if (strlen($functionName) > 0) {
                $functionIndex = $dataDir."/function.$functionName.index";
                $fileInfos = explode("\n",trim(file_get_contents($functionIndex)));
                if (strlen($className) > 0) { //we search for class::member
                    $commonFileInfos = [];
                    if (file_exists($functionIndex)) {
                        foreach($fileInfos as $fileInfo) {
                            $file = explode(":", $fileInfo)[0];
                            if (in_array($file, $classFiles)) {
                                $commonFileInfos[] = $fileInfo;
                            }
                        }
                    }
                    if (count($commonFileInfos) > 0) {
                        $possibleFileInfos = $commonFileInfos;
                    }
                }
            }
        }

        //construct the result
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

    public function clearIndexs() {
        $cmd = "find {$this->dataDir} -type f -name \"*.index\" -delete";
        shell_exec($cmd);
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

    /**
     * save index one time
     */
    public function saveIndex($indexContent) {
        file_put_contents($this->indexPath, $indexContent);
    }

    /**
     * get the index map
     */
    public function getIndexMap() {
        $map = [];
        if (file_exists($this->indexPath)) {
            $map = json_decode(file_get_contents($this->indexPath), true);
        }
        return $map;
    }
}
