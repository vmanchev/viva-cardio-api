<?php

use Phalcon\Config\Adapter\Php as ConfigPhp;

$env = getenv('APPLICATION_ENV');

if (!$env) {
    $env = 'development';
}

$fileName = 'config/' . $env . '.php';
$config = new ConfigPhp($fileName);
