<?php

$loader->addNamespace('App', __DIR__);
$loader->addNamespace('PHC', __DIR__ . '/../vendors/phc/src');

$config = \FW\Core\Config::getInstance();

$config->set('views-folder', __DIR__ . '/views');
$config->set('scan-folders', [
	__DIR__ . '/controllers',
	__DIR__ . '/services',
	__DIR__ . '/repositories'
]);