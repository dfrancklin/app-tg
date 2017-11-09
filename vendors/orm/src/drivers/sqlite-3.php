<?php

use ORM\Core\Driver;

if (!class_exists('SQLiteDriver_3')) {

	class SQLiteDriver_3 extends Driver
	{

		const NAME = 'SQLite';

		const VERSION = '3';

		public function __construct()
		{
			$this->GENERATE_ID_TYPE = 'ATTR';
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

	}

}

return $driver = new SQLiteDriver_3;
