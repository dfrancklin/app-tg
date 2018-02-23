<?php

namespace ORM\Builders;

use ORM\Orm;

use ORM\Constants\CascadeTypes;

use ORM\Core\Proxy;

use ORM\Mappers\Join;
use ORM\Mappers\Column;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;

class Merge
{

	const UPDATE_TEMPLATE = 'UPDATE %s SET %s WHERE %s = %s';

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
		$this->logger = $this->orm->getLogger();
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

		if (!$this->table->isMutable()) {
			throw new \Exception('The object of the class "' . $this->table->getClass() . '" is not mutable');
		}

		$id = $this->table->getId()->getProperty();

		$this->updateBefore();

		if (!($query = $this->generateQuery())) {
			throw new \Exception('The object of the class "' . $this->table->getClass() . '" seems to be empty');
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

		$this->updateManyToMany();
		$this->updateAfter();

		if ($proxy) {
			$proxy->__setObject($object);
			$object = $proxy;
		}

		return $this->object;
	}

	private function updateManyToMany()
	{
		foreach ($this->table->getJoins('type', 'manyToMany') as $join) {
			$property = $join->getProperty();

			if (in_array(CascadeTypes::UPDATE, $join->getCascade())
					&& !empty($this->object->{$property})) {
				$this->updateManyCascade($join);
			}

			if (empty($join->getMappedBy())
					&& !empty($this->object->{$property})) {
				$this->deleteManyToMany($join);
				$this->insertManyToMany($join);
			}
		}
	}

	private function deleteManyToMany(Join $join)
	{
		$reference = $this->orm->getTable($join->getReference());
		$property = $join->getProperty();
		$joinTable = null;
		$column = null;
		$id = null;

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
			$column = $joinTable->getInverseName();
			$id = $reference->getId()->getProperty();
		} else {
			$joinTable = $join->getJoinTable();
			$column = $joinTable->getJoinName();
			$id = $this->table->getId()->getProperty();
		}

		$joinTableName = '';

		if (!empty($joinTable->getSchema())) {
			$joinTableName .= $joinTable->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$joinTableName .= $this->connection->getDefaultSchema() . '.';
		}

		$joinTableName .= $joinTable->getName();
		$bind = ':' . $column;
		$values[$bind] = $this->object->{$id};

		$sql = sprintf(
			Remove::DELETE_TEMPLATE,
			$joinTableName,
			$column,
			$bind
		);

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

	private function insertManyToMany(Join $join)
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
			Persist::INSERT_TEMPLATE,
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

	private function updateBefore()
	{
		foreach ($this->table->getJoins('type', 'belongsTo') as $join) {
			if (in_array(CascadeTypes::UPDATE, $join->getCascade())) {
				$this->updateCascade($join);
			}
		}
	}

	private function updateAfter()
	{
		foreach ($this->table->getJoins('type', 'hasOne') as $join) {
			if (in_array(CascadeTypes::UPDATE, $join->getCascade())) {
				$this->updateCascade($join);
			}
		}

		foreach ($this->table->getJoins('type', 'hasMany') as $join) {
			if (in_array(CascadeTypes::DELETE, $join->getCascade())) {
				$this->deleteManyCascade($join);
			}

			if (in_array(CascadeTypes::UPDATE, $join->getCascade())) {
				$this->updateManyCascade($join);
			}
		}
	}

	private function updateCascade(Join $join)
	{
		$property = $join->getProperty();
		$value = $this->object->{$property};
		$this->object->{$property} = $this->_merge($join, $value);
	}

	private function deleteManyCascade($join)
	{
		$refTable = $this->orm->getTable($join->getReference());
		$refId = $refTable->getId()->getProperty();

		$id = $this->table->getId()->getProperty();
		$value = $this->object->{$id};

		$oldObject = $this->em->find($this->table->getClass(), $value);

		$property = $join->getProperty();

		$newValues = $this->object->{$property};
		$oldValues = $oldObject->{$property};

		if (is_null($newValues)) {
			return;
		}

		if (!is_null($newValues) && !is_array($newValues)) {
			$newValues = [];
		}

		if (is_array($oldValues)) {
			foreach ($oldValues as $old) {
				$found = false;

				foreach ($newValues as $new) {
					if ($new->{$refId} === $old->{$refId}) {
						$found = true;
						break;
					}
				}

				if (!$found) {
					$this->em->remove($old);
				}
			}
		}
	}

	private function updateManyCascade(Join $join)
	{
		$property = $join->getProperty();
		$values = $this->object->{$property};

		if (!is_array($values)) {
			return;
		}

		foreach($values as $key => $value) {
			$this->object->{$property}[$key] = $this->_merge($join, $value);
		}
	}

	private function _merge(Join $join, $value)
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
		$builder = Merge::class;

		if (!$this->em->find($class, $value->{$id})) {
			if (in_array(CascadeTypes::INSERT, $join->getCascade())) {
				$builder = Persist::class;
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

		$newValue = $builder->exec($value, $this->original);

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
		$idName = $idBind = null;
		$sets = $values = [];
		$driver = $this->connection->getDriver();

		foreach (array_merge($this->table->getColumns(), $this->table->getJoins()) as $column) {
			if ($column instanceof Join && $column->getType() !== 'belongsTo') {
				continue;
			}

			if ($column instanceof Column && $column->isId()) {
				$idName = $column->getProperty();
				$idBind = ':' . $column->getProperty();
				$values[$idBind] = $this->object->{$column->getProperty()};

				continue;
			}

			if ($column instanceof Join) {
				$join = $this->object->{$column->getProperty()};

				if (!is_null($join)) {
					if (is_object($join)) {
						$class = $column->getReference();
						$reference = $this->orm->getTable($class);
						$id = $reference->getId()->getProperty();

						if (!empty($join->{$id})) {
							$name = $column->getName();
							$bind = ':' . $name;
							$sets[] = sprintf('%s = %s', $name, $bind);
							$values[$bind] = $join->{$id};
						}
					} else {
						$name = $column->getName();
						$bind = ':' . $name;
						$sets[] = sprintf('%s = %s', $name, $bind);
						$values[$bind] = null;
					}
				}
			} else {
				$name = $column->getName();
				$bind = ':' . $name;
				$value = $this->object->{$column->getProperty()};

				$sets[] = sprintf('%s = %s', $name, $bind);
				$values[$bind] = $driver->convertFromType($value, $column->getType());
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
			self::UPDATE_TEMPLATE,
			$tableName,
			implode(', ', $sets),
			$idName,
			$idBind
		);
		$this->values = $values;

		return !empty($sets) ? $query : false;
	}

}
