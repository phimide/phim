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

    public static function getInstance($projectHash) {
        if (!isset(self::$instance)) {
            self::$instance = new self($projectHash);
        }
        return self::$instance;
    }

    public function __construct($projectHash) {
        $this->projectHash = $projectHash;
        $this->projectIndexFile = Config::get('dataRoot').'/'.$projectHash.'/project.index';
    }

    public function getProjectHash() {
        return $this->projectHash;
    }

    public function getFileExtensions() {
        return $this->fileExtensions;
    }

    /**
     * get the project index
     */
    public function getIndex() {
        $projectIndexData = file_get_contents($this->projectIndexFile);
        $result = json_decode($projectIndexData, true);

        return $result;
    }

    /**
     * save the project index
     */
    public function saveIndex($indexData) {
        file_put_contents($this->projectIndexFile, json_encode($indexData));
    }
}
