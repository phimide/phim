<?php
$rootDir = __DIR__;

require_once $rootDir.'/../vendor/autoload.php';

$loader = new Composer\Autoload\ClassLoader();
$loader->add("Core", $rootDir. "/../src");
$loader->register();

$config = require $rootDir.'/../config/config.php';
$bootstrap = new Core\Bootstrap($config);
$bootstrap->init();
