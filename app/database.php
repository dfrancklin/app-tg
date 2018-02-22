<?php

$config = \FW\Core\Config::getInstance();

$orm = \ORM\Orm::getInstance();

$connectionFile = $config->get('connection-file');
$logLocation = $config->get('log-location');
$logLevel = $config->get('log-level');

if ($connectionFile) {
	$orm->setConnectionsFile($connectionFile);
}

if ($logLocation) {
	$orm->setLogLocation($logLocation);
}

if ($logLevel) {
	$orm->setLogLevel($logLevel);
}

$initDatabase = new \App\Helpers\InitDatabase;

$orm->setConnection('sqlite', [
	'namespace' => 'App\\Models',
	'modelsFolder' => __DIR__ . '/models/',
	'create' => true,
	'beforeDrop' => [$initDatabase, 'beforeDrop'],
	'afterCreate' => [$initDatabase, 'afterCreate']
]);

$dm = \FW\Core\DependenciesManager::getInstance();
$dm->value(\ORM\Orm::class, $orm);
