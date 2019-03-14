<?php
namespace Core;

class Logger
{
    private $logFile;

    public function __construct($config) {
        $logDir = dirname($config['log']);
        $logFileName = basename($config['log']);
        $cmd = "find $logDir -type d | head -n 1";
        $absoluteLogDir = trim(shell_exec($cmd));
        $this->logFile = $absoluteLogDir.'/'.$logFileName;
    }

    public function log($message) {
        file_put_contents($this->logFile, $message."\n", \FILE_APPEND);
    }
}
