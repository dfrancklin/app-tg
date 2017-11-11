<?php

include_once __DIR__ . '/config.php';

$fw = \FW\FW::getInstance();
$config = \FW\Core\Config::getInstance();

if ($scanFolders = $config->get('scan-folders')) {
	$fw->scanComponents(...$scanFolders);
}

$fw->run();