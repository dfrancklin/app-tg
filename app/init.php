<?php

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/database.php';

$fw = \FW\FW::getInstance();
$config = \FW\Core\Config::getInstance();

if ($scanFolders = $config->get('scan-folders')) {
	$fw->scanComponents(...$scanFolders);
}

// $fw->enableWatch(__DIR__ . '/..');
$fw->static('/public', [
	__DIR__ . '/../resources/',
	__DIR__ . '/../vendors/phc/src/resources/'
]);
$fw->run();
