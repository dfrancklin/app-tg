<?php

spl_autoload_register(function ($class) {
	$root = 'ORM';
	$srcFolder = 'src';

	if (substr($class, 0, strlen($root)) !== $root) {
		return;
	}

	$classInfo = explode('\\', $class);
	$className = array_pop($classInfo);
	$namespace = strtolower(implode(DIRECTORY_SEPARATOR, $classInfo));
	$folder = substr_replace($namespace, $srcFolder, 0, strlen($root));
	$file = __DIR__ . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $className . '.php';

	if (file_exists($file)) {
		include $file;
	}
});
