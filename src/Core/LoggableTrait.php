<?php
namespace Core;

trait LoggableTrait
{
    protected $logger;

    public function setLogger($logger) {
        $this->logger = $logger;
    } 
}
