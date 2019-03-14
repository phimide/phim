<?php
namespace Service;

use \Core\BaseService as BaseService;

class CreateIndex extends BaseService
{
    public function start() {
        $dataDir = $this->project->getDataDir();

        $targetDirectory = $this->project->getPath();
        //find all php files
        $cmd = "find $targetDirectory -type f -name \"*.php\" -not -path \"*.git*\"";
        $output = shell_exec($cmd);
        $fileList = explode("\n", trim($output));
        $functionFinder = '/function[\s\n]+(\S+)[\s\n]*\(/';
        $classFinders = [
            '/class[\s\n]+(.*)[a-zA-Z0-9,\s\n]*{/',
            '/interface[\s\n]+(.*)[\s\n]*{/',
            '/trait[\s\n]+(.*)[\s\n]*{/'
        ];
        $functionsHash = [];
        $classesHash = [];
        foreach($fileList as $file) {
            $content = file_get_contents($file);
            # Find all php functions
            preg_match_all( $functionFinder , $content , $matches, \PREG_OFFSET_CAPTURE);
            if (count($matches) > 1) {
                $matches = $matches[1];
                foreach($matches as $match) {
                    $functionName = $match[0];
                    $charPos = $match[1];
                    list($before) = str_split($content, $charPos);
                    $lineNumber = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
                    //exclude constructor
                    if (!in_array($functionName, ['__construct'])) {
                        $functionsHash[$functionName][] = [$file, $lineNumber];
                    }
                }
            }

            # Find all php classes
            foreach($classFinders as $classFinder) {
                preg_match_all( $classFinder, $content , $matches, \PREG_OFFSET_CAPTURE);
                if (count($matches) > 1) {
                    $matches = $matches[1];
                    foreach($matches as $match) {
                        $chunk = $match[0];
                        $charPos = $match[1];
                        $className = explode(" ", $chunk)[0];
                        $lineNumber = count(explode("\n", substr($content, 0, $charPos)));
                        $classesHash[$className][] = [$file, $lineNumber];
                    }
                }
            }
        }

        $data = [
            'functions' => $functionsHash,
            'classes' => $classesHash
        ];

        $this->project->saveIndex($data);
    }
}
