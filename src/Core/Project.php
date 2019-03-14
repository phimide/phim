<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

class Project {
    private $path;
    private $fileExtensions;
    private $dataRoot;
    private $projectIndexFile;

    public function __construct($info, $dataRoot) {
        $projectDetails = explode("|", $info);
        $path = trim($projectDetails[0]);
        $path = $this->getRealPath($path);
        $this->path = $path;
        $this->fileExtensions = explode(",", trim($projectDetails[1]));
        $this->dataRoot = $dataRoot;
        $dataDir = $this->getRealPath($dataRoot.'/projects').'/'.md5($this->path);
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
        $result = JSON_decode(file_get_contents($this->projectIndexFile), true);
        return $result;
    }

    /**
     * save the project index
     */
    public function saveIndex($indexData) {
        file_put_contents($this->projectIndexFile, JSON_encode($indexData));
    }

    private function getRealPath($path) {
        $cmd = "find $path -type d | head -n 1";
        $path = trim(shell_exec($cmd));
        return $path;
    }
}
