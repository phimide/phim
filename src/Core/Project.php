<?php
/**
 * project info
 * format: [path] | (file extension 1),(file extension 2)
 */
namespace Core;

class Project {
    private $path;
    private $fileExtensions;

    public function __construct($info) {
        $projectDetails = explode("|", $info);
        $this->path = $projectDetails[0];
        $this->fileExtensions = explode(",", $projectDetails[1]);
    }

    public function getPath() {
        return $this->path;
    }

    public function getFileExtensions() {
        return $this->fileExtensions;
    }
}
