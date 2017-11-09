<?php

namespace ORM\Interfaces;

use ORM\Core\Driver;

interface IConnection
{

	function prepare(String $sql) : \PDOStatement;

	function lastInsertId() : String;

	function beginTransaction() : bool;

	function commit() : bool;

	function rollback() : bool;

	function getDriver() : Driver;

	function getDefaultSchema() : ?String;

}
