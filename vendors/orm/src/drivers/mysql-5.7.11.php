<?php

use ORM\Constants\GeneratedTypes;
use ORM\Core\Driver;

if (!class_exists('MySQLDriver_5_7_11')) {

	class MySQLDriver_5_7_11 extends Driver
	{

		private static $instance;

		const NAME = 'MySQL';

		const VERSION = '5.7.11';

		private function __construct()
		{
			$this->GENERATE_ID_TYPE = GeneratedTypes::ATTR;
			$this->GENERATE_ID_ATTR = 'AUTO_INCREMENT';
			$this->PAGE_TEMPLATE = '%s ' . "\n" . 'LIMIT %d, %d';
			$this->TOP_TEMPLATE = '%s ' . "\n" . 'LIMIT %d';
			$this->DATA_TYPES = [
				'string' => 'VARCHAR(%d)',
				'int' => 'INTEGER',
				'float' => 'DOUBLE',
				'lob' => 'TEXT',
				'date' => 'DATE',
				'time' => 'TIME',
				'datetime' => 'DATETIME',
				'bool' => 'TINYINT(1)',
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
			$this->validateFields(['db', 'host', 'schema', 'user', 'pass'], $config);
			$dsn = "$config[db]:host=$config[host];dbname=$config[schema]";

			return $this->createConnection($dsn, $config['user'] ?? null, $config['pass'] ?? null);
		}

	}

}

return MySQLDriver_5_7_11::class;
