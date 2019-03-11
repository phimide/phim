<?php
namespace Core;

class BaseService
{
    protected $options;

    public function __construct($options) {
        $this->options = $options;
    }
}
