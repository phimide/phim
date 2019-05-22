<?php
namespace Util;

use Util\MessageEncoder;

class WordSearchEngine
{
    const REDIS_KEY_PREFIX = "phim_index_";

    private $projectHash;
    private $dataRoot;
    private $dataDir;
    private $indexFilePath;

    public function __construct($projectHash, $dataRoot) {
        $this->projectHash = $projectHash;
        $this->dataRoot = $dataRoot;
        $this->dataDir = $this->dataRoot.'/'.$this->projectHash;
        $this->indexFilePath = $this->dataRoot.'/'.$this->projectHash.'.index';
    }

    public function doSearch($file, $contextLine, $contextPosition) {
        if (!file_exists($this->indexFilePath)) {
            return "";
        }
        $indexMap = $this->getIndexMap();
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
                }
            }
        }

        $result = $this->getResultFromFileInfos($possibleFileInfos);

        return $result;
    }

    protected function getIndexMap() {
        $result = [];
        try {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $key = static::REDIS_KEY_PREFIX."_".$this->projectHash;
            if ($redis->exists($key)) {
                //redis key exists, fetch from redis
                $result = MessageEncoder::decode($redis->get($key));
            } else {
                //redis key does not exist, generate it
                $indexContent = file_get_contents($this->indexFilePath);
                $redis->set($key, $indexContent);
                $result = MessageEncoder::decode($indexContent);
            }
        } catch(\RedisException $e) {
            $result = MessageEncoder::decode(file_get_contents($this->indexFilePath));
        }
        return $result;
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
