<?php

$config = \FW\Core\Config::getInstance();

$orm = \ORM\Orm::getInstance();

$connectionFile = $config->get('connection-file');
$logFile = $config->get('log-file');

if ($connectionFile) {
	$orm->setConnectionsFile($connectionFile);
}

if ($logFile) {
	$orm->setLogger($logFile, \ORM\Logger\Logger::ERROR);
}

$initDatabase = new \App\Helpers\InitDatabase;

$orm->setConnection('sqlite', [
	'namespace' => 'App\\Models',
	'modelsFolder' => __DIR__ . '/models/',
	'create' => true,
	'beforeDrop' => [$initDatabase, 'beforeDrop'],
	'afterCreate' => [$initDatabase, 'afterCreate']
]);

vd($orm); die();

$dm = \FW\Core\DependenciesManager::getInstance();
$dm->value(\ORM\Orm::class, $orm);
