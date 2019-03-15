<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

class Project {
    use LoggableTrait;

    private $path;
    private $fileExtensions;
    private $dataRoot;
    private $cacheConfig;
    private $projectIndexFile;
    private $projectHash;

    public function __construct($info, $dataRoot, $cacheConfig) {
        $projectDetails = explode("|", $info);
        $path = trim($projectDetails[0]);
        $path = $this->getRealPath($path);
        $this->path = $path;
        $this->fileExtensions = explode(",", trim($projectDetails[1]));
        $this->dataRoot = $dataRoot;
        $this->cacheConfig = $cacheConfig;
        $this->projectHash = md5($this->path);
        $dataDir = $this->getRealPath($dataRoot.'/projects').'/'.$this->projectHash;
        system("mkdir -p {$dataDir}");
        $this->dataDir = $dataDir;
        $this->projectIndexFile = $this->dataDir.'/project.index';
    }

    public function getPath() {
        return $this->path;
    }

    public function getFileExtensions() {
        return $this->fileExtensions;
    }

    public function getDataDir() {
        return $this->dataDir;
    }

    /**
     * get the project index
     */
    public function getIndex() {
        //first, check in memory storage
        $projectIndexDataFound = false;
        $projectIndexData = "";
        try {
            $cache = InMemoryDataStore::getInstance($this->cacheConfig);
            $key = $this->getProjectIndexDataKey();
            $projectIndexData = $cache->get($key);
        } catch (\RedisException $e) {
            $this->logger->log("Redis Error: ".$e->getTraceAsString());
        }

        if (strlen($projectIndexData) > 0) {
            $projectIndexDataFound = true;
        }

        if (!$projectIndexDataFound) {
            //index data not found in memory, get it from the file
            $projectIndexData = file_get_contents($this->projectIndexFile);
        }

        $result = json_decode($projectIndexData, true);

        return $result;
    }

    /**
     * save the project index
     */
    public function saveIndex($indexData) {
        $indexDataJSON = JSON_encode($indexData);
        //persist to file
        file_put_contents($this->projectIndexFile, $indexDataJSON);
        //also save in memory
        try {
            $cache = InMemoryDataStore::getInstance($this->cacheConfig);
            $key = $this->getProjectIndexDataKey();
            $cache->set($key, $indexDataJSON);
        } catch (\RedisException $e) {
            $this->logger->log("Redis Error: ".$e->getTraceAsString());
        }
    }

    private function getProjectIndexDataKey() {
        return 'phim-project-data-index-'.$this->projectHash;
    }

    private function getRealPath($path) {
        $cmd = "find $path -type d | head -n 1";
        $path = trim(shell_exec($cmd));
        return $path;
    }
}
