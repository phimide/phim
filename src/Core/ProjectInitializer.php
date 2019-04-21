<?php
namespace Core;

class ProjectInitializer
{
    public function init($info, $dataRoot) {
        $projectInfo = ProjectInfoParser::parse($info);
        $projectHash = $projectInfo['projectHash'];
        $projectPath = $projectInfo['projectPath'];
        $fileExtensions = $projectInfo['fileExtensions'];
        $dataDir = $dataRoot.'/'.$projectHash;
        system("mkdir -p {$dataDir}");

        $project = new Project($projectHash, $dataRoot);

        //create the project index
        $this->createIndex($projectPath, $fileExtensions, $project);
    }

    /**
     * save the project index
     */
    public function createIndex($projectPath, $fileExtensions, $project) {
        //find all php files
        $cmd = "find $projectPath -type f -name \"*.php\" -not -path \"*.git*\"";
        $output = shell_exec($cmd);
        $fileList = explode("\n", trim($output));
        $functionFinder = '/function[\s\n]+(.[a-zA-Z0-9_\-]+)[\s\n]*\(/';
        $classFinders = [
            '/class[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9_,\-\s\n]*{/',
            '/interface[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{/',
            '/trait[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{/'
        ];

        $project->clearIndexs();
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
                        $indexContent = $file.':'.$lineNumber."\n";
                        $project->saveSingleIndex('function',$functionName,$indexContent);
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
                        if (strlen($className) > 0) {
                            $lineNumber = count(explode("\n", substr($content, 0, $charPos)));
                            $indexContent = $file.':'.$lineNumber."\n";
                            $project->saveSingleIndex('class',$className,$indexContent);
                        }
                    }
                }
            }
        }
    }
}
