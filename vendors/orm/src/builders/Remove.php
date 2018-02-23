<?php

namespace ORM\Builders;

use ORM\Orm;

use ORM\Constants\CascadeTypes;

use ORM\Core\Proxy;

use ORM\Mappers\Join;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;

class Remove
{

	const DELETE_TEMPLATE = 'DELETE FROM %s WHERE %s = %s';

	private $em;

	private $orm;

	private $proxy;

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

	public function exec($object, $original = null) : int
	{
		if (!is_object($object)) {
			return 0;
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
		$this->proxy = $proxy;
		$this->object = $object;
		$this->original = $original ?? $object;
		$this->table = $this->orm->getTable($class);

		if (!$this->table->isMutable()) {
			throw new \Exception('The object of the class "' . $this->table->getClass() . '" is not mutable');
		}

		if (!$this->proxy) {
			foreach ($this->table->getJoins('type', 'belongsTo') as $join) {
				$property = $join->getProperty();
				$values[$property] = $this->object->{$property};
			}

			$this->proxy = new Proxy($this->em, $this->object, $values);
		}

		$rows = 0;

		$rows += $this->removeManyToMany();
		$rows += $this->removeBefore();

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

		if (!($rowCount = $statement->rowCount())) {
			throw new \Exception('Something went wrong while removing a register');
		}

		$rows += $rowCount;

		$rows += $this->removeAfter();

		return $rows;
	}

	private function removeManyToMany() : int
	{
		$rows = 0;

		foreach ($this->table->getJoins('type', 'manyToMany') as $join) {
			$property = $join->getProperty();

			if (in_array(CascadeTypes::DELETE, $join->getCascade())) {
				$rows += $this->removeManyCascade($join);
			}

			$this->deleteManyToMany($join);
		}

		return $rows;
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
			self::DELETE_TEMPLATE,
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

	private function removeBefore() : int
	{
		$rows = 0;

		foreach ($this->table->getJoins('type', 'hasOne') as $join) {
			if (in_array(CascadeTypes::DELETE, $join->getCascade())) {
				$rows += $this->removeCascade($join);
			}
		}

		foreach ($this->table->getJoins('type', 'hasMany') as $join) {
			if (in_array(CascadeTypes::DELETE, $join->getCascade())) {
				$rows += $this->removeManyCascade($join);
			}
		}

		return $rows;
	}

	private function removeAfter() : int
	{
		$rows = 0;

		foreach ($this->table->getJoins('type', 'belongsTo') as $join) {
			if (in_array(CascadeTypes::DELETE, $join->getCascade())) {
				$rows += $this->removeCascade($join);
			}
		}

		return $rows;
	}

	private function removeCascade(Join $join) : int
	{
		$property = $join->getProperty();
		$value = $this->proxy->{$property};

		return $this->_remove($join, $value);
	}

	private function removeManyCascade(Join $join) : int
	{
		$property = $join->getProperty();
		$values = $this->proxy->{$property};

		if (!is_array($values)) {
			return 0;
		}

		$rows = 0;

		foreach($values as $key => $value) {
			$rows += $this->_remove($join, $value);
		}

		return $rows;
	}

	private function _remove(Join $join, $value) : int
	{
		if (!is_object($value)) {
			return 0;
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

		if (!$this->em->find($class, $value->{$id})) {
			return 0;
		}

		$remove = new Remove($this->connection, $this->em);
		$rows = $remove->exec($proxy ?? $value, $this->original);

		return $rows;
	}

	private function generateQuery() : ?String
	{
		$idName = $idBind = null;
		$values = [];

		foreach ($this->table->getColumns() as $column) {
			if ($column->isId()) {
				$idName = $column->getProperty();
				$idBind = ':' . $column->getProperty();
				$values[$idBind] = $this->object->{$column->getProperty()};

				break;
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
			self::DELETE_TEMPLATE,
			$tableName,
			$idName,
			$idBind
		);
		$this->values = $values;

		return ($idName && $idBind) ? $query : null;
	}

}
