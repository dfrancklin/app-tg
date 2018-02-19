<?php

namespace ORM\Interfaces;

use ORM\Builders\Query;

use ORM\Interfaces\IConnection;

interface IEntityManager
{

	function find(String $class, $id);

	function list(String $class);

	function createQuery(String $class = null, String $alias = null) : Query;

	function remove($object);

	function save($object);

	function beginTransaction() : bool;

	function commit() : bool;

	function rollback() : bool;

}
