<?php
namespace Core;

use Util\FileUtil;

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
        $fileExtensionsStr = implode("|", $fileExtensions);
        $result = [];
        $finders = [
            'function' => 'function[\s\n]+(.[a-zA-Z0-9_\-]+)[\s\n]*\(',
            'class' => 'class[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9_,\-\s\n\\\\\\\\]*{',
            'interface' => 'interface[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{',
            'trait' => 'trait[\s\n]+(.[a-zA-Z0-9_\-]+)[a-zA-Z0-9,\-\s\n]*{',
        ];

        foreach($finders as $type => $pattern) {
            $cmd = "cd $projectPath; ag --skip-vcs-ignores -G '\.($fileExtensionsStr)$' \"".$pattern."\" | grep \"$type \"";
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
                        $realKey = "";
                        if ($type === "function") {
                            $realKey = "function.$key.index";
                        } else {
                            $realKey = "class.$key.index";
                        }
                        $result[$realKey][] = $line;
                    }
                }
            }
        }

        $project->saveIndex(json_encode($result));
    }
}
