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

    public function __construct($info, $dataRoot) {
        $projectDetails = explode("|", $info);
        $this->path = $projectDetails[0];
        $this->fileExtensions = explode(",", $projectDetails[1]);
        $this->dataRoot = $dataRoot;
        $this->dataDir = $dataRoot.'/projects/'.md5($this->path);
        system('mkdir -p '.$this->dataDir);
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
}
