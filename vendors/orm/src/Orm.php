<?php

namespace ORM;

use ORM\Builders\TableManager;

use ORM\Core\Connection;
use ORM\Core\Driver;
use ORM\Core\EntityManager;

use ORM\Helpers\Annotation;

use ORM\Mappers\Table;

use ORM\Logger\Logger;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;
use ORM\Interfaces\ILogger;

class Orm
{

	private static $instance;

	private $tables;

	private $connectionsFile;

	private $connections;

	private $defaultConnection;

	private $logConfig;

	private $logger;

	protected function __construct()
	{
		$this->tables = [];
		$this->connections = [];
		$this->logConfig = [
			'location' => __DIR__ . '/../',
			'filename' => 'orm',
			'level' => Logger::LEVEL_INFO,
			'occurrency' => Logger::OCCURRENCY_DAILY,
			'disabled' => false
		];
	}

	public static function getInstance() : Orm
	{
		if (is_null(self::$instance)) {
			self::$instance = new Orm();
		}

		return self::$instance;
	}

	public function setConnectionsFile(String $file)
	{
		if (!file_exists($file)) {
			throw new \Exception('The connections\' file "' . $file . '" does not exists');
		}

		$this->connectionsFile = $file;
	}

	public function setConnection(String $name, Array $config = [])
	{
		$this->defaultConnection = $name;
		$this->addConnection($name, $config);
	}

	public function addConnection(String $name, Array $config = [])
	{
		$this->connections[$name] = $this->createConnection($name);

		if (!empty($config) && isset($config['create']) && $config['create']) {
			if (!isset($config['namespace'])) {
				throw new \Exception('In order to drop (optional) and create the "namespace" must be informed');
			}

			if (!isset($config['modelsFolder'])) {
				throw new \Exception('In order to drop (optional) and create the "models folder" must be informed');
			}

			$table = new TableManager($this->connections[$name], $config['namespace'], $config['modelsFolder']);

			if (!empty($config) && isset($config['drop']) && $config['drop']) {
				$callback = $this->createCallback($config['beforeDrop'] ?? null, $name);
				$table->drop($callback);
			}

			$callback = $this->createCallback($config['afterCreate'] ?? null, $name);
			$table->create($callback);
		}
	}

	private function createCallback($callback, String $name) : ?\Closure
	{
		if (!empty($callback)) {
			if (
				$callback instanceof \Closure ||
				(
					is_string($callback) &&
					function_exists($callback)
				) ||
				(
					is_array($callback) &&
					is_string($callback[0]) &&
					class_exists($callback[0]) &&
					method_exists(...$callback)
				) ||
				(
					is_array($callback) &&
					is_object($callback[0]) &&
					method_exists(...$callback)
				)
			) {
				$em = $this->createEntityManager($name);
				$orm = $this;

				return function() use ($callback, $em, $orm) {
					$callback($em, $orm);
				};
			}
		}

		return null;
	}

	public function setDefaultConnection(String $name)
	{
		$this->defaultConnection = $name;
	}

	private function createConnection(String $name) : IConnection
	{
		$config = $this->getConfiguration($name);
		$driver = $this->loadDriver($config['db'], $config['version'] ?? null);
		$pdo = $driver->getConnection($config);

		return new Connection($pdo, $driver, $config['schema'] ?? null);
	}

	private function getConfiguration(String $name = null) : Array
	{
		if (empty($name)) {
			$name = $this->defaultConnection;
		}

		if (empty($this->connectionsFile)) {
			$this->setConnectionsFile(__DIR__ . '/../connection.config.php');
		}

		$connections = require $this->connectionsFile;

		if (empty($connections)) {
			throw new \Exception('There are no connection definitions found');
		}

		if (!isset($connections[$name])) {
			throw new \Exception('There is no connection definition with such name "' . $name . '"');
		}

		return $connections[$name];
	}

	private function loadDriver(String $db, String $version) : Driver
	{
		$ds = DIRECTORY_SEPARATOR;
		$driverPath = __DIR__ . $ds . 'drivers' . $ds;
		$driverFile = $driverPath . $db;

		if (!empty($version)) {
			$driverFile .= '-' . $version;
		}

		$driverFile .= '.php';

		if (!file_exists($driverFile)) {
			$driverFile = $driverPath . $db . '.php';

			if (!file_exists($driverFile)) {
				$message = 'The driver file for "' . $db . '"';

				if (!empty($version)) {
					$message .= ' on version "' . $version . '"';
				}

				$message .= ' nor a generic Driver was not found!';

				throw new \Exception($message);
			}
		}

		$driverClass = require $driverFile;

		if (empty($driverClass)) {
			throw new \Exception('The driver class was not found on driver file "' . $driverFile . '"');
		}

		$driver = $driverClass::getInstance();

		if (!($driver instanceof Driver)) {
			throw new \Exception('The driver instance must be an implementation of "' . Driver::class . '"');
		}

		return $driver;
	}

	public function getConnection(String $name = null) : IConnection
	{
		if (empty($name)) {
			$name = $this->defaultConnection;
		}

		if (empty($this->connections)) {
			$this->setConnection($name);
		}

		if (!array_key_exists($name, $this->connections)) {
			$this->addConnection($name);
		}

		if (isset($this->connections[$name])) {
			return $this->connections[$name];
		}

		throw new \Exception('There is no connection with such name "' . $name . '"');
	}

	public function getTable(String $class) : Table
	{
		if (!$class) {
			throw new \Exception('You must inform the class name');
		}

		if (!array_key_exists($class, $this->tables)) {
			$annotation = new Annotation($class);
			$table = $annotation->mapper();
			$this->tables[$class] = $table;
		}

		return $this->tables[$class];
	}

	public function createEntityManager(String $connectionName = null) : IEntityManager
	{
		return new EntityManager($this->getConnection($connectionName));
	}

	public function getLogger()
	{
		if (!$this->logger) {
			$this->logger = new Logger(...array_values($this->logConfig));
		}

		return $this->logger;
	}

	public function setLogDisable(bool $disabled)
	{
		$this->logConfig['disabled'] = $disabled;
	}

	public function setLogLocation(String $location)
	{
		$this->logConfig['location'] = $location;
	}

	public function setLogFilename(String $filename)
	{
		$this->logConfig['filename'] = $filename;
	}

	public function setLogLevel(int $level)
	{
		$this->logConfig['level'] = $level;
	}

	public function setLogOccurrency(int $occurrency)
	{
		$this->logConfig['occurrency'] = $occurrency;
	}

}
