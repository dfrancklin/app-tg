<?php

namespace ORM\Builders;

use ORM\Orm;

use ORM\Constants\CascadeTypes;

use ORM\Core\Proxy;

use ORM\Mappers\Column;
use ORM\Mappers\Join;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;

class Persist
{

	const INSERT_TEMPLATE = 'INSERT INTO %s (%s) VALUES (%s)';

	private $em;

	private $orm;

	private $table;

	private $object;

	private $original;

	private $connection;

	public function __construct(IConnection $connection, IEntityManager $em)
	{
		if (!$connection) {
			throw new \Exception('Conexão não definida');
		}

		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->connection = $connection;
	}

	public function exec($object, $original = null)
	{
		if (!is_object($object)) {
			return;
		}

		$proxy = null;

		if ($object instanceof Proxy) {
			$proxy = $object;
			$object = $object->__getObject();
		}

		if (!is_null($original) && $object === $original) {
			if ($proxy) {
				$proxy->__setObject($object);
				$object = $proxy;
			}

			return $object;
		}

		$class = get_class($object);
		$this->object = $object;
		$this->original = $original ?? $object;
		$this->table = $this->orm->getTable($class);

		if ($this->table->isMutable()) {
			throw new \Exception('The object of the class "' . $this->table->getClass() . '" is not mutable');
		}

		$id = $this->table->getId();
		$prop = $id->getProperty();

		$this->persistBefore();

		if (!($query = $this->generateQuery())) {
			throw new \Exception('The object of the class "' . $this->table->getClass() . '" seems to be empty');
		}

		if ($id->isGenerated()) {
			$this->object->{$prop} = $this->fetchNextId();
		}

		$statement = $this->connection->prepare($query);
		$executed = $statement->execute($this->values);

		if (isset($this->logger)) {
			$log = $query;

			if (!empty($this->values)) {
				$log .= "\n" . print_r($this->values, true);
			}

			$this->logger->debug($log, static::class);
		}

		if (!$statement->rowCount()) {
			throw new \Exception('Something went wrong while persistting a register');
		}

		if ($id->isGenerated()) {
			$lastId = $this->connection->lastInsertId();

			if ($id->getType() === 'int') {
				$this->object->{$prop} = (int) $lastId;
			} else {
				$this->object->{$prop} = $lastId;
			}
		}

		$this->persistManyToMany();
		$this->persistAfter();

		if ($proxy) {
			$proxy->__setObject($object);
			$object = $proxy;
		}

		return $this->object;
	}

	private function fetchNextId()
	{
		if (in_array($this->connection->getDriver()->GENERATE_ID_TYPE, ['QUERY', 'SEQUENCE'])) {
			$statement = $this->connection->prepare($this->connection->getDriver()->GENERATE_ID_QUERY);
			$executed = $statement->execute();

			if (isset($this->logger)) {
				$this->logger->debug($this->connection->getDriver()->GENERATE_ID_QUERY, static::class);
			}

			if ($executed) {
				$next = $statement->fetch(\PDO::FETCH_NUM);

				if (!empty($next)) {
					return $next[0];
				}
			}
		}

		return null;
	}

	private function persistManyToMany()
	{
		foreach ($this->table->getJoins('type', 'manyToMany') as $join) {
			$property = $join->getProperty();

			if (in_array(CascadeTypes::INSERT, $join->getCascade()) &&
					!empty($this->object->{$property})) {
				$this->persistManyCascade($join);
			}

			if (empty($join->getMappedBy()) &&
					!empty($this->object->{$property})) {
				$this->insertManyToMany($join);
			}
		}
	}

	public function insertManyToMany(Join $join)
	{
		$reference = $this->orm->getTable($join->getReference());
		$property = $join->getProperty();
		$joinTable = null;

		if ($join->getMappedBy()) {
			$referenceJoin = null;
			$referenceJoins = $reference->getJoins('reference', $this->table->getClass());

			foreach ($referenceJoins as $j) {
				if ($j->getType() === 'manyToMany') {
					$referenceJoin = $j;
				}
			}

			if (empty($referenceJoin)) {
				return;
			}

			$joinTable = $referenceJoin->getJoinTable();
		} else {
			$joinTable = $join->getJoinTable();
		}

		$joinTableName = '';

		if (!empty($joinTable->getSchema())) {
			$joinTableName .= $joinTable->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$joinTableName .= $this->connection->getDefaultSchema() . '.';
		}

		$joinTableName .= $joinTable->getName();
		$columns = [$joinTable->getJoinName(), $joinTable->getInverseName()];
		$binds = [':' . $joinTable->getJoinName(), ':' . $joinTable->getInverseName()];
		$sql = sprintf(
			self::INSERT_TEMPLATE,
			$joinTableName,
			implode(', ', $columns),
			implode(', ', $binds)
		);

		$id = $this->table->getId()->getProperty();
		$referenceId = $reference->getId()->getProperty();

		foreach($this->object->{$property} as $p) {
			$values = [];
			$values[':' . $joinTable->getJoinName()] = $this->object->{$id};
			$values[':' . $joinTable->getInverseName()] = $p->{$referenceId};

			$statement = $this->connection->prepare($sql);
			$statement->execute($values);

			if (isset($this->logger)) {
				$log = $sql;

				if (!empty($values)) {
					$log .= "\n" . print_r($values, true);
				}

				$this->logger->debug($log, static::class);
			}
		}
	}

	private function persistBefore()
	{
		foreach ($this->table->getJoins('type', 'belongsTo') as $join) {
			if (in_array(CascadeTypes::INSERT, $join->getCascade())) {
				$this->persistCascade($join);
			}
		}
	}

	private function persistAfter()
	{
		foreach ($this->table->getJoins('type', 'hasOne') as $join) {
			if (in_array(CascadeTypes::INSERT, $join->getCascade())) {
				$this->persistCascade($join);
			}
		}

		foreach ($this->table->getJoins('type', 'hasMany') as $join) {
			if (in_array(CascadeTypes::INSERT, $join->getCascade())) {
				$this->persistManyCascade($join);
			}
		}
	}

	private function persistCascade(Join $join)
	{
		$property = $join->getProperty();
		$value = $this->object->{$property};
		$this->object->{$property} = $this->_persist($join, $value);
	}

	private function persistManyCascade(Join $join)
	{
		$property = $join->getProperty();
		$values = $this->object->{$property};

		if (!is_array($values)) {
			return;
		}

		foreach($values as $key => $value) {
			$this->object->{$property}[$key] = $this->_persist($join, $value);
		}
	}

	private function _persist(Join $join, $value)
	{
		if (!is_object($value)) {
			return $value;
		}

		$property = $join->getProperty();
		$reference = $join->getReference();

		$proxy = null;

		if ($value instanceof Proxy) {
			$proxy = $value;
			$value = $value->__getObject();
		}

		$class = get_class($value);

		if ($class !== $reference) {
			throw new \Exception('The type of the property "' . $this->table->getClass() .'::' . $property . '"
									should be "' . $reference . '", but "' . $class . '" was given');
		}

		$table = $this->orm->getTable($class);
		$id = $table->getId()->getProperty();
		$builder = Persist::class;

		if ($this->em->find($class, $value->{$id})) {
			if (in_array(CascadeTypes::UPDATE, $join->getCascade())) {
				$builder = Merge::class;
			} else {
				if ($proxy) {
					$proxy->__setObject($value);
					$value = $proxy;
				}

				return $value;
			}
		}

		$builder = new $builder($this->connection, $this->em);

		if ($this->logger) {
			$builder->logger = $this->logger;
		}

		if ($builder instanceof Persist) {
			$newValue = $builder->exec($value, $this->original);
		} else {
			$newValue = $builder->exec($value);
		}

		if ($newValue) {
			if ($proxy) {
				$proxy->__setObject($newValue);
				$newValue = $proxy;
			}

			return $newValue;
		}
	}

	private function generateQuery() : String
	{
		$columns = [];
		$binds = [];
		$values = [];

		foreach (array_merge($this->table->getColumns(), $this->table->getJoins()) as $column) {
			if ($column instanceof Join && $column->getType() !== 'belongsTo') {
				continue;
			}

			if ($column instanceof Join && !empty($this->object->{$column->getProperty()})) {
				$class = $column->getReference();
				$reference = $this->orm->getTable($class);
				$id = $reference->getId();
				$prop = $id->getProperty();
				$join = $this->object->{$column->getProperty()};

				if (!empty($join->$prop)) {
					$columns[] = $column->getName();
					$binds[] = ':' . $column->getName();
					$values[':' . $column->getName()] = $join->$prop;
				}
			} elseif (!empty($this->object->{$column->getProperty()}) || ($column instanceof Column && $column->isId())) {
				$columns[] = $column->getName();
				$binds[] = ':' . $column->getName();
				$value = $this->object->{$column->getProperty()};
				$values[':' . $column->getName()] = $this->convertValue($value, $column->getType());
			}
		}

		$tableName = '';

		if (!empty($this->table->getSchema())) {
			$tableName .= $this->table->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$tableName .= $this->connection->getDefaultSchema() . '.';
		}

		$tableName .= $this->table->getName();

		$query = sprintf(
			self::INSERT_TEMPLATE,
			$tableName,
			implode(', ', $columns),
			implode(', ', $binds)
		);
		$this->values = $values;

		return !empty($columns) ? $query : false;
	}

	private function convertValue($value, String $type)
	{
		if ($value instanceof \DateTime) {
			$format = $this->connection->getDriver()->FORMATS[$type] ?? 'Y-m-d';

			return $value->format($format);
		} elseif (is_bool($value)) {
			return $value ? 1 : 0;
		} else {
			return $value;
		}
	}

}
