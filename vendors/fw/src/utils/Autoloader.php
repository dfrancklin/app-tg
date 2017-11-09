<?php

namespace FW\Utils;

class Autoloader {

	protected $prefixes = array();

	private static $instance;

	protected function __construct() {}

	public static function getInstance() : self {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register() {
		spl_autoload_register(array($this, 'loadClass'));
	}

	public function addNamespace($prefix, $baseDir, $prepend = false) {
		$prefix = trim($prefix, '\\') . '\\';

		$baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if (isset($this->prefixes[$prefix]) === false) {
			$this->prefixes[$prefix] = [];
		}

		if ($prepend) {
			array_unshift($this->prefixes[$prefix], $baseDir);
		} else {
			array_push($this->prefixes[$prefix], $baseDir);
		}
	}

	public function loadClass($class) {
		$prefix = $class;

		while (false !== $pos = strrpos($prefix, '\\')) {
			$prefix = substr($class, 0, $pos + 1);
			$relativeClass = substr($class, $pos + 1);
			$mappedFile = $this->loadMappedFile($prefix, $relativeClass);

			if ($mappedFile) {
				return $mappedFile;
			}

			$prefix = rtrim($prefix, '\\');
		}

		return false;
	}

	protected function loadMappedFile($prefix, $relativeClass) {
		if (isset($this->prefixes[$prefix]) === false) {
			return false;
		}

		foreach ($this->prefixes[$prefix] as $baseDir) {
			$relativeClass = explode('\\', $relativeClass);

			if (count($relativeClass) > 1) {
				foreach ($relativeClass as $i => $item) {
					if ($i < count($relativeClass) - 1) {
						$relativeClass[$i] = strtolower($item);
					}
				}
			}

			$relativeClass = implode('\\', $relativeClass);

			$file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $baseDir . $relativeClass) . '.php';

			if ($this->requireFile($file)) {
				return $file;
			}
		}

		return false;
	}

	protected function requireFile($file) {
		if (file_exists($file)) {
			require $file;
			return true;
		}

		return false;
	}

}
