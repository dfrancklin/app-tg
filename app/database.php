<?php

$config = \FW\Core\Config::getInstance();

$orm = \ORM\Orm::getInstance();

$connectionFile = $config->get('connection-file');
$logFile = $config->get('log-file');
$logLevel = $config->get('log-level');

if ($connectionFile) {
	$orm->setConnectionsFile($connectionFile);
}

if ($logFile) {
	$orm->setLogger($logFile, $logLevel ?? \ORM\Logger\Logger::INFO);
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
