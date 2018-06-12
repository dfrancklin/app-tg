<?php

namespace ORM\Builders;

use ORM\Orm;

use ORM\Constants\GeneratedTypes;

use ORM\Mappers\Column;
use ORM\Mappers\Join;
use ORM\Mappers\Table;

use ORM\Interfaces\IConnection;

class TableManager
{

	const CHECK_IF_TABLE_EXISTS_TEMPLATE = 'SELECT * FROM %s';

	const CREATE_TABLE_TEMPLATE = 'CREATE TABLE %s (%s)';

	const DROP_TABLE_TEMPLATE = 'DROP TABLE %s';

	const DROP_SEQUENCE_TEMPLATE = 'DROP SEQUENCE %s';

	const FOREIGN_KEY_CONSTRAINT_TEMPLATE = 'FOREIGN KEY (%s) REFERENCES %s (%s)';

	private $orm;

	private $classes;

	private $tables;

	private $connection;

	private $droped;

	private $created;

	public function __construct(IConnection $connection, String $namespace, String $modelsFolder)
	{
		$this->orm = Orm::getInstance();
		$this->classes = $this->loadClasses($namespace, $modelsFolder);
		$this->tables = $this->loadTables();
		$this->connection = $connection;

		$this->droped = [];
		$this->created = [];
	}

	public function drop(?\Closure $callback=null)
	{
		$drops = [];
		$driver = $this->connection->getDriver();

		foreach ($this->tables as $table) {
			$_drops = $this->resolveDropTable($table);
			$drops = array_merge($drops, $_drops);
		}

		if ($driver->GENERATE_ID_TYPE === GeneratedTypes::SEQUENCE) {
			$drops[] = $this->resolveDropSequence($driver->SEQUENCE_NAME);
		}

		if (!empty($drops) && !empty($callback)) {
			$callback();
		}

		foreach($drops as $drop) {
			$statement = $this->connection->prepare($drop);
			$statement->execute();
		}
	}

	public function create(?\Closure $callback=null)
	{
		$creates = [];
		$driver = $this->connection->getDriver();

		foreach ($this->tables as $table) {
			$_creates = $this->resolveCreateTable($table);
			$creates = array_merge($creates, $_creates);
		}

		if ($driver->GENERATE_ID_TYPE === GeneratedTypes::SEQUENCE) {
			$creates[] = $this->resolveCreateSequence($driver->SEQUENCE_NAME);
		}

		foreach($creates as $create) {
			$statement = $this->connection->prepare($create);
			$statement->execute();
		}

		if (!empty($creates) && !empty($callback)) {
			$callback();
		}
	}

	private function resolveCreateTable(Table $table) : Array
	{
		if (in_array($table->getClass(), $this->created)) {
			return [];
		}

		$this->created[] = $table->getClass();

		$creates = [];
		$columns = [];
		$foreigns = [];
		$driver = $this->connection->getDriver();
		$exists = false;
		$tableName = '';

		if (!empty($table->getSchema())) {
			$tableName .= $table->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$tableName .= $this->connection->getDefaultSchema() . '.';
		}

		$tableName .= $table->getName();

		foreach ($table->getColumns() as $column) {
			$columns[] = $this->resolveCreateColumn($column);
		}

		foreach ($table->getJoins('type', 'belongsTo') as $join) {
			$reference = $join->getReference();

			if (!isset($this->tables[$reference])) {
				$this->tables[$reference] = $this->orm->getTable($reference);
			}

			$reference = $this->tables[$reference];
			$id = $reference->getId();
			$referenceTableName = '';

			if (!empty($reference->getSchema())) {
				$referenceTableName .= $reference->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$referenceTableName .= $this->connection->getDefaultSchema() . '.';
			}

			$referenceTableName .= $reference->getName();

			$_creates = $this->resolveCreateTable($reference);
			$creates = array_merge($creates, $_creates);

			$columns[] = $this->resolveCreateColumn($id, $join);

			if ($driver->FK_ENABLE) {
				$foreigns[] = sprintf(
					"\n\t" . self::FOREIGN_KEY_CONSTRAINT_TEMPLATE,
					$join->getName(),
					$referenceTableName,
					$id->getName()
				);
			}
		}

		$columns = array_merge($columns, $foreigns);

		if (!$this->checkIfExists($tableName)) {
			$creates[] = sprintf(
				self::CREATE_TABLE_TEMPLATE,
				$tableName,
				implode(', ', $columns)
			);
		}

		foreach ($table->getJoins('type', 'manyToMany') as $join) {
			if (empty($join->getMappedBy())) {
				$create = $this->resolveJoinTable($table, $join);

				if (!empty($create)) {
					$creates[] = $create;
				}
			}
		}

		return $creates;
	}

	private function resolveCreateColumn(Column $column, Join $join = null, String $name = null) : String
	{
		$driver = $this->connection->getDriver();

		if (empty($join) && empty($name)) {
			$definition = $column->getName();
		} elseif (!empty($join) && empty($name)) {
			$definition = $join->getName();
		} else {
			$definition = $name;
		}

		if (!($column->isId() && $driver->IGNORE_ID_DATA_TYPE && empty($join) && empty($name))) {
			$type = $column->getType();

			if (!array_key_exists($type, $driver->DATA_TYPES)) {
				throw new \Exception('The type "' . $type . '" is not supported on the "' . $driver::NAME . '" driver with the version ' . $driver::VERSION);
			}

			$dataType = $driver->DATA_TYPES[$type];

			if (substr_count($dataType, '%d') === 1) {
				$dataType = sprintf($dataType, $column->getLength());
			} elseif (substr_count($dataType, '%d') === 2) {
				$dataType = sprintf($dataType, $column->getScale(), $column->getPrecision());
			}

			$definition .= ' ' . $dataType;
		} elseif ($column->isId() && $driver->IGNORE_ID_DATA_TYPE && empty($join) && empty($name)) {
			$definition .= ' ' . $driver->GENERATE_ID_ATTR;
		}

		if (empty($join) && empty($name)) {
			$definition .= ' ' . ($column->isNullable() ? 'NULL' : 'NOT NULL');
		} elseif (!empty($join) && empty($name)) {
			$definition .= ' ' . ($join->isOptional() ? 'NULL' : 'NOT NULL');
		} else {
			$definition .= ' NOT NULL';
		}

		if (empty($join) && empty($name)) {
			if ($column->isUnique() && !$column->isId()) {
				$definition .= ' UNIQUE';
			}

			if ($column->isId()) {
				$definition .= ' PRIMARY KEY';
			}

			if (
				$column->isGenerated() &&
				$driver->GENERATE_ID_TYPE === GeneratedTypes::ATTR &&
				!$driver->IGNORE_ID_DATA_TYPE
			) {
				$definition .= ' ' . $driver->GENERATE_ID_ATTR;
			}
		}

		return "\n\t" . $definition;
	}

	private function resolveJoinTable(Table $table, Join $join) : ?String
	{
		$driver = $this->connection->getDriver();
		$referenceClass = $join->getReference();
		$joinTable = $join->getJoinTable();

		if (!isset($this->tables[$referenceClass])) {
			$this->tables[$referenceClass] = $this->orm->getTable($referenceClass);
		}

		$reference = $this->tables[$referenceClass];

		$joinTableName = '';
		$tableName = '';
		$referenceTableName = '';

		if (!empty($joinTable->getSchema())) {
			$joinTableName .= $joinTable->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$joinTableName .= $this->connection->getDefaultSchema() . '.';
		}

		$joinTableName .= $joinTable->getName();

		if (!empty($table->getSchema())) {
			$tableName .= $table->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$tableName .= $this->connection->getDefaultSchema() . '.';
		}

		$tableName .= $table->getName();

		if (!empty($reference->getSchema())) {
			$referenceTableName .= $reference->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$referenceTableName .= $this->connection->getDefaultSchema() . '.';
		}

		$referenceTableName .= $reference->getName();

		$columns[] = $this->resolveCreateColumn(
			$table->getId(),
			null,
			$joinTable->getJoinName()
		);
		$columns[] = $this->resolveCreateColumn(
			$reference->getId(),
			null,
			$joinTable->getInverseName()
		);

		if ($driver->FK_ENABLE) {
			$foreigns[] = sprintf(
				"\n\t" . self::FOREIGN_KEY_CONSTRAINT_TEMPLATE,
				$joinTable->getJoinName(),
				$tableName,
				$table->getId()->getName()
			);
			$foreigns[] = sprintf(
				"\n\t" . self::FOREIGN_KEY_CONSTRAINT_TEMPLATE,
				$joinTable->getInverseName(),
				$referenceTableName,
				$reference->getId()->getName()
			);
		}

		$columns = array_merge($columns, $foreigns);

		if (!$this->checkIfExists($joinTableName)) {
			$create = sprintf(
				self::CREATE_TABLE_TEMPLATE,
				$joinTableName,
				implode(', ', $columns)
			);

			return $create;
		}

		return null;
	}

	private function resolveCreateSequence(String $sequenceName) : String
	{
		return sprintf(self::CREATE_SEQUENCE_TEMPLATE, $sequenceName);
	}

	private function resolveDropTable(Table $table, Join $join = null) : Array
	{
		if (in_array($table->getClass(), $this->droped)) {
			return [];
		}

		$this->droped[] = $table->getClass();
		$drops = [];

		foreach ($table->getJoins() as $_join) {
			if ($_join->getType() === 'belongsTo') {
				continue;
			}

			$reference = $_join->getReference();

			if (in_array($reference, $this->droped)) {
				continue;
			}

			if (!isset($this->tables[$reference])) {
				$this->tables[$reference] = $this->orm->getTable($reference);
			}

			$_drops = $this->resolveDropTable($this->tables[$reference], $_join);
			$drops = array_merge($drops, $_drops);
		}

		if ($join && $join->getType() === 'manyToMany') {
			$joinTable = null;

			if (empty($join->getMappedBy())) {
				$joinTable = $join->getJoinTable();
			} else {
				$reference = $join->getReference();

				if (!isset($this->tables[$reference])) {
					$this->tables[$reference] = $this->orm->getTable($reference);
				}

				$_table = $this->tables[$reference];
				$_joins = $_table->getJoins('property', $join->getMappedBy());

				if (!empty($_joins) && count($_joins) === 1) {
					$_join = $_joins[0];
					$joinTable = $_join->getJoinTable();
				}
			}

			if ($joinTable) {
				$joinTableName = '';

				if (!empty($joinTable->getSchema())) {
					$joinTableName .= $joinTable->getSchema() . '.';
				} elseif (!empty($this->connection->getDefaultSchema())) {
					$joinTableName .= $this->connection->getDefaultSchema() . '.';
				}

				$joinTableName .= $joinTable->getName();

				if ($this->checkIfExists($joinTableName)) {
					$drops[] = sprintf(self::DROP_TABLE_TEMPLATE, $joinTableName);
				}
			}
		}

		$tableName = '';

		if (!empty($table->getSchema())) {
			$tableName .= $table->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$tableName .= $this->connection->getDefaultSchema() . '.';
		}

		$tableName .= $table->getName();

		if ($this->checkIfExists($tableName)) {
			$drops[] = sprintf(self::DROP_TABLE_TEMPLATE, $tableName);
		}

		return $drops;
	}

	private function resolveDropSequence(String $sequenceName) : String
	{
		return sprintf(self::DROP_SEQUENCE_TEMPLATE, $sequenceName);
	}

	public function checkIfExists(String $tableName) : bool
	{
		try {
			$query = sprintf(self::CHECK_IF_TABLE_EXISTS_TEMPLATE, $tableName);
			$statement = $this->connection->prepare($query);
			$executed = $statement->execute();

			return $executed;
		} catch (\Exception $e) {
			return false;
		}
	}

	private function loadClasses(String $namespace, String $folder) : Array
	{
		$ds = DIRECTORY_SEPARATOR;
		$classes = [];

		foreach (scandir($folder) as $file) {
			if (in_array($file, ['.', '..'])) {
				continue;
			}

			$file = explode('.', $file);
			$extension = array_pop($file);
			$file = implode('.', $file);

			if ($extension !== 'php') {
				continue;
			}

			$classes[] = $namespace . '\\' . $file;
		}

		return $classes;
	}

	private function loadTables() : Array
	{
		$tables = [];

		foreach ($this->classes as $class) {
			$table = $this->orm->getTable($class);

			if (!$table->isMutable()) {
				$tables[$class] = $table;
			}
		}

		return $tables;
	}

}
