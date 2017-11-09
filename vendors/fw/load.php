<?php

if (!class_exists('Autoloader')) {
	include_once __DIR__ . '/src/utils/Autoloader.php';
}

use \FW\Utils\Autoloader;

$loader = Autoloader::getInstance();
$loader->register();
$loader->addNamespace('FW', __DIR__ . '/src');

include_once __DIR__ . '/src/config.php';
