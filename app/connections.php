<?php

return $connections = [
	'default' => [
		'db' => 'mysql',
		'version' => '5.7.11',
		'host' => 'localhost',
		'schema' => 'db',
		'user' => 'root',
		'pass' => 'root'
	],
	'sqlite' => [
		'db' => 'sqlite',
		'version' => '3',
		'file' => __DIR__ . '/data/book-store.sq3',
	],
];
