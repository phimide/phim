<?php
namespace Core;

class Bootstrap
{
    private $config;
    private $serviceName;

    public function __construct($config) {
        $this->config = $config;
        $this->cli = \Garden\Cli\Cli::create();
        foreach($config as $command => $commandInfo) {
            $this->serviceName = $commandInfo['service'];
            $this->cli->command($command);
            $this->cli->description($commandInfo['description']);
            foreach($commandInfo['options'] as $optionValue => $optionDetails) {
                $this->cli->opt($optionValue, $optionDetails['description'], $optionDetails['require']);
            }
        }
    }

    public function init() {
        $args = $this->cli->parse($GLOBALS['argv']);
        $serviceName = $this->serviceName;
        $serviceClass = "Service\\$serviceName";
        $service = new $serviceClass($args->getOpts());
        $service->start();
    }
}
