<?php

namespace FW;

use \FW\Core\DependenciesManager;
use \FW\Core\Router;
use \FW\Core\Config;

class FW
{

	private static $instance;

	private $dm;

	private $router;

	private $config;

	private $components;

	private $folders;

	private $watchFolders;

	private $session;

	protected function __construct()
	{
		$this->dm = DependenciesManager::getInstance();
		$this->router = Router::getInstance();
		$this->config = Config::getInstance();
		$this->components = ['Controller', 'Service', 'Repository', 'Factory', 'Component'];
		$this->folders = [];
		$this->watchFolders = [];
		$this->session = new \FW\Storage\Strategies\SessionStorageStrategy('watch');
	}

	public static function getInstance() : self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function scanComponents(String ...$folders)
	{
		if (!count($folders)) {
			throw new \Exception('At least 1 folder needs to be informed');
		}

		$this->folders = array_merge($this->folders, $folders);

		return $this;
	}

	public function enableWatch(...$folders)
	{
		$this->config->set('watching', true);
		$this->watchFolders = $folders;

		return $this;
	}

	public function run()
	{
		$this->resolveScan();

		if ($this->config->get('watching')) {
			$this->resolveWatch();
		}

		$path = $_SERVER['PATH_INFO'] ?? $_SERVER['REDIRECT_URL'] ?? '/';

		if ($path === '/--has-changes-to-watched-files' && $this->config->get('watching')) {
			$hasChanges = $this->compareWatch();

			if ($hasChanges) {
				$this->resolveWatch(true);
				echo 'true';
			} else {
				echo 'false';
			}

			return;
		}

		$page = '';

		try {
			$page = $this->router->handle($path, $_SERVER['REQUEST_METHOD']);
		} catch (\Throwable $e) {
			$page .= $e;
		}

		if (
			$this->config->get('watching') &&
			(
				$this->isHTML($page) ||
				!$this->isJSON($page)
			)
		) {
			$page = $this->addWatchScript($page);
		}

		echo $page;
	}

	private function isHTML($page){
		return $page != strip_tags($page) ? true : false;
	}

	private function isJSON($json){
		json_decode($json);

		return (json_last_error() === JSON_ERROR_NONE);
	}

	private function resolveScan()
	{
		$systemFolders = $this->config->get('system-folders');

		$this->scanComponents(...$systemFolders);

		foreach ($this->folders as $folder) {
			$this->lookUp($folder, true);
		}
	}

	private function lookUp($folder, $recursive = false)
	{
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

	private function resolveEntry($entry)
	{
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

	private function resolveWatch(bool $reload = false)
	{
		if (!$this->session || !empty($this->session->get('files')) && !$reload) {
			return;
		}

		$files = $this->loadWatchedFiles();
		$this->session->save('files', $files);
	}

	private function compareWatch() : bool
	{
		$hasChanges = false;

		$files = $this->loadWatchedFiles();
		$cached = $this->session->get('files');

		foreach ($files as $file => $time) {
			if (!isset($cached[$file])) {
				$hasChanges = true;
				break;
			}

			if (!($cached[$file] === $files[$file])) {
				$hasChanges = true;
				break;
			}
		}

		return $hasChanges;
	}

	private function loadWatchedFiles()
	{
		$files = [];

		foreach ($this->watchFolders as $folder) {
			$_files = $this->loadModificationTime($folder);
			$files = array_merge($files, $_files);
		}

		return $files;
	}

	private function loadModificationTime(String $folder)
	{
		$times = [];

		foreach (glob($folder . '/*') as $entry) {
			if (is_dir($entry)) {
				$_times = $this->loadModificationTime($entry);
				$times = array_merge($times, $_times);
			} else {
				$times[$entry] = filemtime($entry);
			}
		}

		return $times;
	}

	public function addWatchScript($page)
	{
		$path['protocol'] = 'http' . (!empty($_SERVER['HTTPS']) ? 's' : '') . '://';
		$path['server'] = $_SERVER['SERVER_NAME'];
		$path['port'] = $_SERVER['SERVER_PORT'] !== '80' ? $_SERVER['SERVER_PORT'] : '';
		$path['uri'] = '/--has-changes-to-watched-files';

		$url = implode('', $path);

		$script = "<script>
			(function() {
				xhttp = new XMLHttpRequest();

				xhttp.onreadystatechange = _ => {
					if (xhttp.readyState === 4 && xhttp.status === 200) {
						if (xhttp.response === 'true') {
							console.log('refreshing...');
							setTimeout(_=> window.location.reload(), 500);
						}
					}
				};

				const refreshInterval = setInterval(_ => {
					xhttp.open('GET', '$url', true);
					xhttp.send();
				}, 1000);
			})();
		</script>";

		if (strpos('</head>', $page)) {
			list($top, $botton) = preg_split('/<\/head>/i', $page);

			return implode('', [$top, $script, '</head>', $botton]);
		} else {
			return $script . $page;
		}
	}

}
