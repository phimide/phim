<?php
namespace Service;

use \Core\BaseService as BaseService;

class Search extends BaseService
{
    public function start() {
        $project = $this->getProject();
        $path = trim(shell_exec('pwd '.$project->getPath()));
        $extendsions = $project->getFileExtensions();

        $file = $this->options['file'];
        $line = $this->options['line'];

        $lines = explode("\n",trim(file_get_contents($file)));

        $contextLine = $lines[$line - 1];
        $contextPosition = $this->options['pos'];

        $contextLine = str_replace('::',' ', $contextLine);

        $leftPart = substr($contextLine, 0, $contextPosition - 1);
        $rightPart = substr($contextLine, $contextPosition - 1, strlen($contextLine));

        $leftTokens = explode(" ", $leftPart);
        $rightTokens = explode(" ", $rightPart);

        $wordLeftPart = array_pop($leftTokens);
        $wordRightPart = array_shift($rightTokens);

        $word = $wordLeftPart.$wordRightPart;

        //now remove all "(,),;"
        $word = str_replace(['(',')',';'], "",$word);
    }
}