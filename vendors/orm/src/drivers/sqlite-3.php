<?php

use ORM\Constants\GeneratedTypes;
use ORM\Core\Driver;

if (!class_exists('SQLiteDriver_3')) {

	class SQLiteDriver_3 extends Driver
	{

		private static $instance;

		const NAME = 'SQLite';

		const VERSION = '3';

		private function __construct()
		{
			$this->GENERATE_ID_TYPE = GeneratedTypes::ATTR;
			$this->GENERATE_ID_ATTR = 'AUTOINCREMENT';
			$this->FK_ENABLES = false;
			$this->PAGE_TEMPLATE = '%s ' . "\n" . 'LIMIT %d, %d';
			$this->TOP_TEMPLATE = '%s ' . "\n" . 'LIMIT %d';
			$this->DATA_TYPES = [
				'string' => 'TEXT',
				'int' => 'INTEGER',
				'float' => 'REAL',
				'lob' => 'BLOB',
				'date' => 'TEXT',
				'time' => 'TEXT',
				'datetime' => 'TEXT',
				'bool' => 'INTEGER'
			];
		}

		public static function getInstance() : Driver
		{
			if (!self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function getConnection(Array $config) : \PDO
		{
			$this->validateFields(['db', 'file'], $config);
			$dsn = "$config[db]:$config[file]";

			$pdo = new \PDO($dsn, $config['user'] ?? null, $config['pass'] ?? null);

			$pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_EMPTY_STRING);

			return $pdo;
		}

	}

}

return SQLiteDriver_3::class;
