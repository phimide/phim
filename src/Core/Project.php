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

    public function searchWord($contextLine, $contextPosition) {
        $projectDB = new ProjectDB();
        $word = $this->getWordFromLineAndPosition($contextLine, $contextPosition);
        $wordSplits = explode("::", $word);
        $wordSplitsCount = count($wordSplits);
        if ($wordSplitsCount === 2) {
            //pattern: class::member
            $classPath = $wordSplits[0];
            $classPathSplits = explode("/", $classPath);
            $className = array_pop($classPathSplits);
            $classIndex = "class.$className.index";
            if (isset($indexMap[$classIndex])) {
                $fileInfos = $indexMap[$classIndex];
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
            $functionIndex = "function.$functionName.index";
            if (isset($indexMap[$functionIndex])) {
                $fileInfos = $indexMap[$functionIndex];
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
            $sql = "SELECT * from {$this->projectTable} WHERE index_type = 'class' AND index_name = '$className'";

            $rows = $projectDB->getRowsFromSQL($sql);

            if (count($rows) > 0) {
                $fileInfos = [];
                foreach($rows as $row) {
                    $fileInfos[] = $row['index_info'];
                }
                $result = implode("\n", $fileInfos);
                return $result;
            } else {
                //no class|trait|interface is matched, try to find in function index
                $functionName = $className;
                $sql = "SELECT * from {$this->projectTable} WHERE index_type = 'function' AND index_name = '$functionName'";
            }

            /*
            if (count($possibleFileInfos) === 0) {
                //no class|trait|interface is matched, try to find in function index
                $functionName = $className;
                $functionIndex = "function.$functionName.index";
                if (isset($indexMap[$functionIndex])) {
                    $fileInfos = $indexMap[$functionIndex];
                    foreach($fileInfos as $fileInfo) {
                        $file = explode(":", $fileInfo)[0];
                        $possibleFileInfos[$file] = $fileInfo;
                    }
                } else {
                    //if there are no result found, simply do a ag search
                    $cmd = "cd {$this->projectPath}; ag \"{$word}\" --skip-vcs-ignores";
                    $output = trim(shell_exec($cmd));
                    $lines = explode("\n", $output);

                    $result = "";
                    $lineNumber = 0;
                    foreach($lines as $line) {
                        if (strlen($line) > 0) {
                            $lineNumber ++;
                            $lineSplits = explode(":", $line);
                            $file = array_shift($lineSplits);
                            $lineLocation = array_shift($lineSplits);
                            $matchInfo = implode(":", $lineSplits);
                            $line  = $this->projectPath."/".$file."(".$lineLocation.") ".$matchInfo;
                            $result .= $lineNumber . ". " .$line."\n";
                        }
                    }

                    $result = trim($result);

                    return $result;
                }
            }
             */

        }
    }

    private function getWordFromLineAndPosition($contextLine, $contextPosition) {
        $leftStoppingSymbolsHash = [
',' => 1,
';' => 1,
' ' => 1,
'[' => 1,
'\'' => 1,
'+' => 1,
')' => 1,
'(' => 1,
'>' => 1,
'!' => 1,
];
        $rightStoppingSymbolsHash = [
            ' ' => 1,
';' => 1,
',' => 1,
'{' => 1,
']' => 1,
'\'' => 1,
')' => 1,
'(' => 1,
'!' => 1,
' ' => 1,
];

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

        //sometimes windows will leave a ^M character, we need to remove it
        $word = str_ireplace("\x0D", "", $word);

        //also, replace \ to /
        $word = str_replace("\\", "/", $word);

        return $word;
    }
}
