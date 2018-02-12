<?php

namespace ORM\Core;

use ORM\Orm;

use ORM\Builders\Merge;
use ORM\Builders\Persist;
use ORM\Builders\Query;
use ORM\Builders\Remove;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;
use ORM\Interfaces\ILogger;

class EntityManager implements IEntityManager
{

	private $connection;

	private $transactionActive;

	public function __construct(IConnection $connection, ILogger $logger)
	{
		$this->connection = $connection;
		$this->logger = $logger;
		$this->orm = Orm::getInstance();
	}

	public function find(String $class, $id)
	{
		$table = $this->orm->getTable($class);
		$alias = strtolower($table->getName()[0]);
		$prop = $alias . '.' . $table->getId()->getProperty();

		$query = $this->createQuery();

		return $query->from($class, $alias)->where($prop)->equals($id)->one();
	}

	public function list(String $class, int $page = null, int $quantity = null)
	{
		$query = $this->createQuery($class);

		if (!empty($page) && !empty($quantity)) {
			$query->page($page, $quantity);
		} elseif (!empty($page) && empty($quantity)) {
			$query->top($page);
		}

		return $query->list();
	}

	public function createQuery(String $class = null) : Query
	{
		$query = new Query($this->connection, $this);

		if ($this->logger) {
			$query->logger = $this->logger;
		}

		if (!empty($class)) {
			$query->from($class);
		}

		return $query;
	}

	public function save($object)
	{
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to save an object');
		}

		if (empty($object)) {
			throw new \Exception('A valid object must be passed as parameter');
		}

		if (is_array($object)) {
			$saved = [];

			foreach ($object as $o) {
				$saved[] = $this->_save($o);
			}
		} else {
			$saved = $this->_save($object);
		}

		return $saved;
	}

	private function _save($object)
	{
		$proxy = null;

		if ($object instanceof Proxy) {
			$proxy = $object;
			$object = $object->__getObject();
		}

		$class = get_class($object);
		$table = $this->orm->getTable($class);

		if (!$table->isMutable()) {
			throw new \Exception('You shouldn\'t save a immutable object of the class "' . $class . '"');
		}

		$id = $table->getId();
		$prop = $id->getProperty();

		if (!empty($object->$prop)) {
			$method = 'merge';
		} else {
			$method = 'persist';
		}

		$saved = $this->$method($object);

		if ($proxy) {
			$proxy->__setObject($saved);
			$saved = $proxy;
		}

		return $saved;
	}

	public function remove($object)
	{
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to save an object');
		}

		if (empty($object)) {
			throw new \Exception('A valid object must be passed as parameter');
		}

		if (is_array($object)) {
			$saved = 0;

			foreach ($object as $o) {
				$saved += $this->_remove($o);
			}
		} else {
			$saved = $this->_remove($object);
		}

		return $saved;
	}

	private function _remove($object)
	{
		$proxy = null;

		if ($object instanceof Proxy) {
			$proxy = $object;
			$object = $object->__getObject();
		}

		$class = get_class($object);
		$table = $this->orm->getTable($class);

		if (!$table->isMutable()) {
			throw new \Exception('You shouldn\'t remove a immutable object of the class "' . $class . '"');
		}

		if ($this->exists($object)) {
			$remove = new Remove($this->connection, $this);

			if ($this->logger) {
				$remove->logger = $this->logger;
			}

			return $remove->exec($proxy ?? $object);
		}

		return 0;
	}

	public function beginTransaction() : bool
	{
		if ($this->transactionActive) {
			throw new \Exception('A transaction is already active');
		}

		if ($this->connection->beginTransaction()) {
			$this->transactionActive = true;

			return true;
		}

		throw new \Exception('Something went wrong while beginning a transaction');
	}

	public function commit() : bool
	{
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to commit');
		}

		if ($this->connection->commit()) {
			$this->transactionActive = false;

			return true;
		}

		throw new \Exception('Something went wrong while committing a transaction');
	}

	public function rollback() : bool
	{
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to rollback');
		}

		if ($this->connection->rollback()) {
			$this->transactionActive = false;

			return true;
		}

		throw new \Exception('Something went wrong while rolling back a transaction');
	}

	private function persist($object)
	{
		if ($this->exists($object)) {
			return $this->merge($object);
		}

		$persist = new Persist($this->connection, $this);

		if ($this->logger) {
			$persist->logger = $this->logger;
		}

		return $persist->exec($object);
	}

	private function merge($object)
	{
		if (!$this->exists($object)) {
			return $this->persist($object);
		}

		$merge = new Merge($this->connection, $this);

		if ($this->logger) {
			$merge->logger = $this->logger;
		}

		return $merge->exec($object);
	}

	private function exists($object) : bool
	{
		$class = get_class($object);
		$table = $this->orm->getTable($class);
		$id = $table->getId();
		$prop = $id->getProperty();
		$rs = $this->find($class, $object->$prop);

		return !!$rs;
	}

}
