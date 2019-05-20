<?php
namespace Core;

class Bootstrap
{
    private $config;
    private $serviceName;
    private $commandInfo;

    public function __construct($config) {
        $numOfArgs = count($GLOBALS['argv']);
        $this->config = $config;
        $this->commandInfo = [];
        $this->cli = \Garden\Cli\Cli::create();
        if ($numOfArgs < 2) {
            $commands = $this->loadAllCommands();
            foreach($commands as $command => $commandInfo) {
                $this->cli->command($command);
                $this->cli->description($commandInfo['description']);
                foreach($commandInfo['options'] as $optionValue => $optionDetails) {
                    $this->cli->opt($optionValue, $optionDetails['description'], $optionDetails['require']);
                }
            }
        } else {
            $command = $GLOBALS['argv'][1];
            $commandDir = $GLOBALS['rootDir']."/../src/Commands/$command";
            $commandConfigFile = "$commandDir/config.php";
            $commandInfo = require_once($commandConfigFile);
            $GLOBALS['loader']->add("Service", "$commandDir"); //lazy loading
            $GLOBALS['loader']->register();
            $this->commandInfo = $commandInfo;
            foreach($commandInfo['options'] as $optionValue => $optionDetails) {
                $this->cli->opt($optionValue, $optionDetails['description'], $optionDetails['require']);
            }
            $this->cli->command($command);
        }
    }

    public function loadAllCommands() {
        $commands = [];
        $commandDir = $GLOBALS['rootDir']."/../src/Commands";
        $entries = explode("\n", trim(shell_exec("find $commandDir -type d -depth 1")));
        foreach($entries as $entry) {
            $key = str_replace("$commandDir/", "", $entry);
            $commands[$key] = require_once("$entry/config.php");
        }
        return $commands;
    }

    public function init() {
        $args = $this->cli->parse($GLOBALS['argv']);
        $this->serviceName = $this->commandInfo['service'];
        $serviceClass = "Service\\{$this->serviceName}";
        $options = $args->getOpts();
        $requirementIsMet = true;
        foreach($options as $key => $val) {
            if ($this->commandInfo['options'][$key]['require'] && strlen($val) === 0) {
                print "Please provide value for $key (--$key)\n";
                $requirementIsMet = false;
                break;
            }
        }
        if ($requirementIsMet) {
            $service = new $serviceClass($options, $this->config);
            $service->start();
        }
    }
}
