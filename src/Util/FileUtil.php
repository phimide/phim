<?php
namespace Util;

class FileUtil
{
    public function getFileListsContent($projectPath, $fileExtensions) {
        $patterns = [];
        foreach($fileExtensions as $extension) {
            $patterns[] = "-name \"*.$extension\"";
        }
        $patternsStr = implode(" -o ", $patterns);
        $cmd = "find -H $projectPath -type f $patternsStr";
        $output = shell_exec($cmd);
        return $output;
    }

    public function getFileLists($projectPath, $fileExtensions) {
        $output = $this->getFileListsContent($projectPath, $fileExtensions);
        $result = explode("\n", trim($output));
        return $result;
    }
}
