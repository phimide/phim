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
            $commandInfo = require_once($commandConfigFile);
            $this->commandInfo = $commandInfo;
            foreach($commandInfo['options'] as $optionValue => $optionDetails) {
                $this->cli->opt($optionValue, $optionDetails['description'], $optionDetails['require']);
            }
            $this->cli->command($command);
        }
    }

    public function loadAllCommands() {
        $this->config;
    }

    public function init() {
        $args = $this->cli->parse($GLOBALS['argv']);
        $command = $args->getCommand();
        $this->serviceName = $this->commandInfo['service'];
        $serviceClass = "Service\\{$this->serviceName}";
        $options = $args->getOpts();
        $requirementIsMet = true;
        foreach($options as $key => $val) {
            if ($this->config['commands'][$command]['options'][$key]['require'] && strlen($val) === 0) {
                print "Please provide value for $key (--$key)\n";
                $requirementIsMet = false;
                break;
            }
        }
        if ($requirementIsMet) {
            $commandDir = $GLOBALS['rootDir']."/../src/Commands/$command";
            $commandConfigFile = "$commandDir/config.php";
            $GLOBALS['loader']->add("Service", "$commandDir/Service"); //lazy loading 
            $GLOBALS['loader']->register();
            $service = new $serviceClass($options, $this->config);
            $service->start();
        }
    }
}
