<?php

namespace FW;

use \FW\Core\DependenciesManager;
use \FW\Core\Router;
use \FW\Core\Config;

class FW {

	private static $instance;

	private $dm;

	private $router;

	private $config;

	private $components;

	private $folders;

	protected function __construct() {
		$this->dm = DependenciesManager::getInstance();
		$this->router = Router::getInstance();
		$this->config = Config::getInstance();
		$this->components = ['Controller', 'Service', 'Repository', 'Factory', 'Component'];
		$this->folders = [];
	}

	public static function getInstance() : self {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function scanComponents(String ...$folders) {
		if (!count($folders)) {
			throw new \Exception('At least 1 folder needs to be informed');
		}

		$this->folders = array_merge($this->folders, $folders);

		return $this;
	}

	private function lookUp($folder, $recursive = false) {
		if (!$folder) {
			throw new \Exception('A folder needs to be informed');
		}

		foreach (glob($folder . DIRECTORY_SEPARATOR . '*') as $entry) {
			if (is_dir($entry) && $recursive) {
				$this->lookUp($folder);
			} else {
				$this->resolveEntry($entry);
			}
		}
	}

	public function run() {
		// check for context folder
		// create .htaccess if not exists
		
		$systemFolders = $this->config->get('system-folders');

		$this->scanComponents(...$systemFolders);

		foreach ($this->folders as $folder) {
			$this->lookUp($folder, true);
		}

		if (!isset($_SERVER['PATH_INFO']) && !isset($_SERVER['REDIRECT_URL'])) {
			$controller = $this->router->handle('/', $_SERVER['REQUEST_METHOD']);
		} else {
			$controller = $this->router->handle($_SERVER['PATH_INFO'] ?? $_SERVER['REDIRECT_URL'], $_SERVER['REQUEST_METHOD']);
		}
	}

	private function resolveEntry($entry) {
		$handler = fopen($entry, 'r');

		if (!$handler) {
			throw new \Exception('Folder "' . $entry . '" does not exists');
		}

		$namespace = $className = null;
		$isComponent = $isController = false;

		while (!feof($handler) && !($className && $isComponent)) {
			$line = fgets($handler);

			if (preg_match('/namespace\s([^;]+)/i', trim($line), $matches)) {
				$namespace = $matches[1];
			} elseif (preg_match('/class\s([^\s]+)/i', trim($line), $matches)) {
				$className = $matches[1];
			} elseif (preg_match('/@(' . implode('|', $this->components) . ')/i', trim($line), $matches)) {
				$isComponent = true;

				if ($matches[0] === '@Controller') {
					$isController = true;
				}
			}
		}

		fclose($handler);

		if (!$isComponent || !$className) {
			return;
		}

		$fullName = $namespace . '\\' . $className;

		$this->dm->register($fullName);

		if($isController) {
			$this->router->register($fullName);
		}
	}

}
