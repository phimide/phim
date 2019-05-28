<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

use Core\ProjectDB;

class Project {
    private $projectHash;
    private $projectTable;
    private $projectPath;
    private $fileExtensions;

    public function __construct($rawProjectInfo) {
        $projectInfo = $this->parse($rawProjectInfo);
        $this->projectHash = $projectInfo['projectHash'];
        $this->projectTable = "phim_ide_project_{$this->projectHash}_index";
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

    public function getProjectPath() {
        return $this->projectPath;
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

    public function createIndex() {
        $fileExtensionsStr = implode("|", $this->fileExtensions);
        $indexMap = [];
        $finders = [
            'function' => 'function[\s\n]+(.[a-zA-Z0-9_\-]+)[\s\n]*\(',
            'class' => 'class[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9_,\-\s\n\\\\\\\\]*{',
            'interface' => 'interface[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{',
            'trait' => 'trait[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{',
        ];

        foreach($finders as $type => $pattern) {
            $realType = $type;
            if ($type !== "function") {
                $realType = "class";
            }

            if (!isset($indexMap[$realType])) {
                $indexMap[$realType] = [];
            }

            $cmd = "cd {$this->projectPath}; ag --skip-vcs-ignores -G '\.($fileExtensionsStr)$' \"".$pattern."\" | grep \"$type \"";
            $output = shell_exec($cmd);
            $lines = explode("\n", trim($output));
            foreach($lines as $line) {
                if (strlen($line) > 0) {
                    $pos = strpos($line, "$type ");
                    $key = substr($line, $pos);
                    $key = str_replace("$type ","", $key);

                    if ($type === 'function') {
                        $comps = explode("(", $key);
                        $key = trim($comps[0]);
                    } else {
                        $comps = explode(" ", $key);
                        $key = trim($comps[0]);
                    }

                    if ($key !== '__construct') {
                        $indexMap[$realType][$key][] = $line;
                    }
                }
            }
        }

        $this->saveIndex($indexMap);
    }

    public function saveIndex($indexMap) {
        $projectDB = new ProjectDB();

        $sql = "
DROP TABLE IF EXISTS {$this->projectTable};
CREATE TABLE {$this->projectTable} (project_hash varchar(32),index_type varchar(10),
    index_name varchar(255),
    index_info text,
    key(project_hash),
    key(index_type),
    key(index_name)
)
";

        $projectDB->doSQL($sql);

        $sql = "INSERT INTO {$this->projectTable}(project_hash,index_type,index_name,index_info) VALUES";
        $valuesArr = [];
        foreach($indexMap as $type => $info) {
            foreach($info as $key => $lines) {
                foreach($lines as $line) {
                    $filteredLine = addslashes($line);
                    $valuesArr[] = "('{$this->projectHash}','$type','$key','$filteredLine')";
                }
            }
        }
        $sql .= implode(",",$valuesArr);
        $projectDB->doSQL($sql);
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
