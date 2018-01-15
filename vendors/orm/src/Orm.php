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

	private $logger;

	protected function __construct()
	{
		$this->tables = [];
		$this->connections = [];
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
		$dsn = $this->getDSN($config);
		$driver = $this->loadDriver($config['db'], $config['version'] ?? null);

		$pdo = new \PDO($dsn, $config['user'] ?? null, $config['pass'] ?? null);

		$pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_EMPTY_STRING);

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

		require $this->connectionsFile;

		if (!isset($connections[$name])) {
			throw new \Exception("Configuração de conexão \"$name\" não definida");
		}

		return $connections[$name];
	}

	private function loadDriver(String $db, String $version) : Driver
	{
		$ds = DIRECTORY_SEPARATOR;
		$driverFile = __DIR__ . $ds . 'drivers' . $ds . $db;

		if (!empty($version)) {
			$driverFile .= '-' . $version;
		}

		$driverFile .= '.php';

		if (!file_exists($driverFile)) {
			$message = 'The driver file for "' . $db . '"';

			if (!empty($version)) {
				$message .= ' on version "' . $version . '"';
			}

			$message .= ' was not found!';

			throw new \Exception($message);
		}

		require $driverFile;

		if (!isset($driver)) {
			throw new \Exception('The driver instance was not found on driver file "' . $driverFile . '"');
		}

		if (!($driver instanceof Driver)) {
			throw new \Exception('The driver instance must be an implementation of "' . Driver::class . '"');
		}

		return $driver;
	}

	private function getDSN(Array $config) : String
	{
		switch ($config['db']) {
			case 'mysql':
				if ($this->validateFields(['db', 'host', 'schema', 'user', 'pass'], $config)) {
					return "$config[db]:host=$config[host];dbname=$config[schema]";
				}
				break;
			case 'sqlite':
			case 'sqlite2':
				if ($this->validateFields(['db', 'file'], $config)) {
					return "$config[db]:$config[file]";
				}
		}

		throw new \Exception('The database "' . $config['db'] . '" is not supported yet');
	}

	private function validateFields(Array $fields, Array $config) : bool
	{
		foreach($fields as $field) {
			if (!isset($config[$field])) {
				throw new \Exception("O campo $config[$field] não foi definido na definição de conexão");
			}
		}

		return true;
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

		throw new \Exception("Não foram encontradas conexões definidas para \"$name\"");
	}

	public function getTable(String $class) : Table
	{
		if (!$class) {
			throw new \Exception('Necessário informar o nome da classe');
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
		if (!$this->logger) {
			$this->setLogger();
		}

		return new EntityManager($this->getConnection($connectionName), $this->logger);
	}

	public function setLogger($file = null, $level = null)
	{
		if (!$file) {
			$file = __DIR__ . '/../log.' . date('Y-m') . '.log';
		}

		$this->logger = new Logger($file, $level);
	}

}
