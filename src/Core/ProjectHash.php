<?php
namespace Core;

class ProjectHash
{
    public static function get($projectInfo) {
        $projectDetails = explode("|", $projectInfo);
        $path = trim($projectDetails[0]);
        $fileExtensionsStr = trim($projectDetails[1]);
        $projectHash = md5($path."|".$fileExtensionsStr);
        return $projectHash;
    }
}
