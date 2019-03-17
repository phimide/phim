<?php
namespace Core;

class ProjectInfoParser
{
    public static function parse($projectInfo) {
        $projectDetails = explode("|", $projectInfo);
        $path = trim($projectDetails[0]);
        $fileExtensionsStr = trim($projectDetails[1]);
        $fileExtensions = explode(",", $fileExtensionsStr);

        $fileExtensionsCount = count($fileExtensions);
        for ($i = 0; $i < $fileExtensionsCount; $i++) {
            $fileExtensions[$i] = trim($fileExtensions[$i]);
        }
        $fileExtensionsStr = implode(",",$fileExtensions);
        $projectHash = md5($path."|".$fileExtensionsStr);
        $result = [
            'projectPath' => $path,
            'projectHash' => $projectHash, 
            'fileExtensions' => $fileExtensions
        ];
        return $result;
    }
}
