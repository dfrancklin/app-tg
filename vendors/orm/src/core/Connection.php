<?php

namespace ORM\Core;

use ORM\Core\Driver;

use ORM\Interfaces\IConnection;

class Connection implements IConnection
{

	private $pdo;

	private $driver;

	private $defaultSchema;

	public function __construct(\PDO $pdo, Driver $driver, ?String $defaultSchema)
	{
		$this->pdo = $pdo;
		$this->driver = $driver;
		$this->defaultSchema = $defaultSchema;
	}

	public function prepare(String $sql) : \PDOStatement
	{
		return $this->pdo->prepare($sql);
	}

	public function lastInsertId() : String
	{
		return $this->pdo->lastInsertId();
	}

	public function beginTransaction() : bool
	{
		return $this->pdo->beginTransaction();
	}

	public function commit() : bool
	{
		return $this->pdo->commit();
	}

	public function rollback() : bool
	{
		return $this->pdo->rollback();
	}

	public function getDriver() : Driver
	{
		return $this->driver;
	}

	public function getDefaultSchema() : ?String
	{
		return $this->defaultSchema;
	}

}
