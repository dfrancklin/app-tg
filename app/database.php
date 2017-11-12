<?php

$config = \FW\Core\Config::getInstance();

$orm = \ORM\Orm::getInstance();

$file = $config->get('connection-file');

if ($file) {
	$orm->setConnectionsFile($file);
}

$initDatabase = new \App\Helpers\InitDatabase;
$orm->setConnection('sqlite', [
	'namespace' => 'App\\Models',
	'modelsFolder' => __DIR__ . '/models/',
	// 'drop' => true,
	'create' => true,
	'beforeDrop' => [$initDatabase, 'beforeDrop'],
	'afterCreate' => [$initDatabase, 'afterCreate']
]);

$dm = \FW\Core\DependenciesManager::getInstance();
$dm->value(\ORM\Orm::class, $orm);
