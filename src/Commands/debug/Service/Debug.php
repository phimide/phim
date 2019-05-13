<?php
namespace Service;

use Core\BaseService as BaseService;

class Debug extends BaseService
{
    private $phimInspectBeginningBlock = "/* phim_inspect_start */";
    private $outputFile;
    private $cmd;

    public function start() {
        $sourceFile = $this->options['file'];
        $lineNumber = $this->options['line'];
        $this->cmd = $this->options['cmd'];

        //clean all previous breakpoints
        $this->cleanBreakpoints($sourceFile);

        //now add breakpoint
        $this->addBreakPoint($sourceFile, $lineNumber);

        //check the syntax of the source file with the breakpoint
        if (!$this->isSyntaxOk($sourceFile)) {
            //there is syntax errors, clean the breakpoint
            $this->cleanBreakpoints($sourceFile);
            print "Error adding breakpoint, syntax error\n";
        } else {
            //run the cmd and forget about the output
            echo shell_exec($this->cmd);
            //clean up the break points again
            $this->cleanBreakpoints($sourceFile);
        }
    }

    private function cleanBreakpoints($sourceFile) {
        $content = file_get_contents($sourceFile);
        $lines = explode("\n", $content);
        $numOfLines = count($lines);
        for ($i = 0; $i < $numOfLines; $i++) {
            $pos = strpos($lines[$i], $this->phimInspectBeginningBlock);
            if ($pos !== FALSE) {
                $lines[$i] = substr($lines[$i], 0, $pos);
            }
        }
        $newFileContent = implode("\n", $lines);

        $oldTimestamp = filemtime($sourceFile);
        file_put_contents($sourceFile, $newFileContent);
        //also, modify the timestamp so that the system will treat it as "unchanged"
        touch($sourceFile, $oldTimestamp);
    }

    private function addBreakPoint($sourceFile, $lineNumber) {
        $phimDebugDir = __DIR__.'/../scripts';
        $content = file_get_contents($sourceFile);
        $lines = explode("\n", $content);
        $exitBlock = "exit";

        $depth = 3;
        if (isset($this->options['depth'])) {
            $depth = $this->options['depth'];
        }

        $inspectBlock = "";

        if (!isset($this->options['variable'])) {
            $phimDebugInspectContent = $this->getDebugInspectScriptContent();
            $inspectBlock = "{$this->phimInspectBeginningBlock} $phimDebugInspectContent phim_debug_inspect(get_defined_vars(), false, $depth);$exitBlock;";
        } else {
            $variableBlock = "";
            //do not include the first $ sign
            if($this->options['variable'][0] === '$') {
                $this->options['variable'] = substr($this->options['variable'],1);
                $variableBlock = '$'.$this->options['variable'];
            } else {
                if (substr($this->options['variable'], 0, 6) === 'self::') {
                    $variableBlock = $this->options['variable'];
                } else if (substr($this->options['variable'], 0, 8) === 'static::') {
                    $variableBlock = $this->options['variable'];
                } else if (substr($this->options['variable'], 0, 8) === 'parent::') {
                    $variableBlock = $this->options['variable'];
                } else {
                    $variableBlock = '$'.$this->options['variable'];
                }
            }
            //just inspect the specific variable
            $phimDebugInspectContent = $this->getDebugInspectScriptContent();
            $inspectBlock = "{$this->phimInspectBeginningBlock} $phimDebugInspectContent \$phim_debug_var=$variableBlock;phim_debug_inspect(\$phim_debug_var, true, $depth, '{$this->options['variable']}');$exitBlock;";
        }

        $lines[$lineNumber - 1] .= $inspectBlock;
        $newFileContent = implode("\n", $lines);
        file_put_contents($sourceFile, $newFileContent);
    }

    private function getDebugInspectScriptContent() {
        $content = file_get_contents(__DIR__ . '/../scripts/phim_debug_inspect.php');
        $content = str_replace("<?php\n","", $content);
        $content = str_replace("\n","", $content);
        return $content;
    }

    private function isSyntaxOk($sourceFile) {
        $output = shell_exec("php -l $sourceFile");
        $result = false;
        if (strpos($output, "No syntax errors detected") !== FALSE) {
            $result = true;
        }
        return $result;
    }
}
