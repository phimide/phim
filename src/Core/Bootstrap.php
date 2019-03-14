<?php
namespace Core;

class Bootstrap
{
    private $config;
    private $serviceName;

    public function __construct($config) {
        $this->config = $config;
        $this->cli = \Garden\Cli\Cli::create();
        $commands = $this->config['commands'];
        foreach($commands as $command => $commandInfo) {
            $this->cli->command($command);
            $this->cli->description($commandInfo['description']);
            foreach($commandInfo['options'] as $optionValue => $optionDetails) {
                $this->cli->opt($optionValue, $optionDetails['description'], $optionDetails['require']);
            }
        }
    }

    public function init() {
        $args = $this->cli->parse($GLOBALS['argv']);
        $command = $args->getCommand();
        $this->serviceName = $this->config['commands'][$command]['service'];
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
            $service = new $serviceClass($options, $this->config);
            //see if this is a project specific service
            if (isset($options['project'])) {
                $project = new Project($options['project'], $this->config['dataRoot'], $this->config['cache']);
                $service->setProject($project);
            }
            $service->start();
        }
    }
}
