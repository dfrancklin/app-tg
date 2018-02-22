<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader->addNamespace('App', __DIR__);
$loader->addNamespace('PHC', __DIR__ . '/../vendors/phc/src');
$loader->addNamespace('ORM', __DIR__ . '/../vendors/orm/src');

$config = \FW\Core\Config::getInstance();

$config->set('views-folder', __DIR__ . '/views');
$config->set('scan-folders', [
	__DIR__ . '/controllers',
	__DIR__ . '/services',
	__DIR__ . '/repositories'
]);

$config->set('connection-file', __DIR__ . '/connections.php');
$config->set('log-location', __DIR__ . '/logs/');
$config->set('log-level', \ORM\Logger\Logger::LEVEL_ERROR);
