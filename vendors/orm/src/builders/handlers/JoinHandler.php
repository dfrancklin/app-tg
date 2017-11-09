<?php

namespace ORM\Builders\Handlers;

use ORM\Builders\Query;

use ORM\Constants\JoinTypes;

use ORM\Mappers\Table;
use ORM\Mappers\Join;

trait JoinHandler
{

	private $joins;

	private $tablesByAlias;

	private $relations;

	private $usedTables;

	public function join(String $join, String $alias, String $type = null) : Query
	{
		if (array_key_exists($alias, $this->tablesByAlias)) {
			throw new \Exception('A class with the alias "' . $alias . '" already exist');
		}

		if (empty($type)) {
			$type = JoinTypes::INNER;
		}

		if (!in_array($type, JoinTypes::TYPES)) {
			throw new \Exception('The join type informed "' . $type . '" does not exists or is not suppoerted');
		}

		$table = $this->orm->getTable($join);

		$this->joins[$join] = [$table, $alias, $type];
		$this->tablesByAlias[$alias] = [$table, $alias, $type];

		return $this;
	}

	public function joins(Array $joins) : Query
	{
		$this->joins = [];

		foreach ($joins as $join) {
			if (!is_array($join)) {
				throw new \InvalidArgumentException('The class name, the alias and the type (optional) must be informed. Ex: [className, alias[, type]]');
			}

			$this->join(...$join);
		}

		return $this;
	}

	private function resolveJoin() : String
	{
		if (empty($this->joins)) {
			return '';
		}

		$this->preProcessJoins($this->target, $this->joins);

		$sql = '';

		foreach ($this->relations as $relation) {
			$sql .= $this->generateJoins(...$relation);
		}

		return $sql;
	}

	private function preProcessJoins($joinInfo, $tables)
	{
		if (is_null($joinInfo)) {
			return;
		}

		$next = array_shift($tables);
		list($table, $alias) = $joinInfo;

		foreach ($table->getJoins() as $join) {
			$name = $alias . '_' . $table->getName() . '.' . $join->getProperty();

			if (!array_key_exists($name, $this->relations)) {
				$reference = $join->getReference();
				$inverse = $this->orm->getTable($reference);
				$_joins = $inverse->getJoins('reference', $table->getClass());

				if (count($_joins)) {
					foreach ($_joins as $_join) {
						$isValid = $this->validTypes($join, $_join);

						if ($isValid) {
							$_inverses = array_filter($this->tablesByAlias, function($join) use ($alias, $inverse) {
								list($_inverse, $_alias) = $join;
								return $_inverse === $inverse && $alias !== $_alias;
							});

							if (!empty($_inverses) && count($_inverses) === 1) {
								$inverseAlias = array_keys($_inverses)[0];
								$inverseName = $inverseAlias . '_' . $inverse->getName() . '.' . $_join->getProperty();

								if (!array_key_exists($inverseName, $this->relations)) {
									if (array_key_exists($join->getReference(), $this->joins)) {
										$sh = $this->joins[$join->getReference()];
										$this->relations[$name] = [$sh, $join];
									}
								}
							}

						}
					}
				}
			}
		}

		return $this->preProcessJoins($next, $tables);
	}

	private function validTypes(Join $join, Join $inverseJoin) : bool
	{
		$valid = false;

		if (($join->getType() === 'hasMany' || $join->getType() == 'hasOne') && $inverseJoin->getType() === 'belongsTo') {
			$valid = true;
		} elseif ($join->getType() === 'belongsTo' && ($inverseJoin->getType() === 'hasMany' || $inverseJoin->getType() == 'hasOne')) {
			$valid = true;
		} elseif ($join->getType() === 'manyToMany' && $inverseJoin->getType() === 'manyToMany') {
			$valid = true;
		}

		return $valid;
	}

	private function generateJoins(Array $joinInfo, Join $join) : String
	{
		list($table, $alias, $joinType) = $joinInfo;
		$referenceClass = $join->getTable()->getClass();
		$referenceAlias = null;

		$_references = array_filter($this->tablesByAlias, function($join) use ($alias, $referenceClass) {
			list($table, $_alias) = $join;

			return $table->getClass() === $referenceClass && $_alias !== $alias;
		});

		if (!empty($_references) && count($_references) === 1) {
			$referenceAlias = array_keys($_references)[0];
		}

		if (empty($referenceAlias)) {
			return '';
		}

		if (array_key_exists($referenceAlias . ':' . $referenceClass, $this->usedTables) &&
				array_key_exists($alias . ':' . $table->getClass(), $this->usedTables) &&
				$join->getType() !== 'manyToMany') {
			return '';
		}

		$method = 'resolveJoin' . ucfirst($join->getType());
		$sql = $this->$method($table, $alias, $join, $referenceAlias, $joinType);
		$this->usedTables[$alias . ':' . $table->getClass()] = $table;

		return $sql;
	}

	private function resolveJoinHasOne(Table $table, String $alias, Join $join, String $joinAlias, String $joinType) : String
	{
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (array_key_exists($alias . ':' . $table->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($join->getTable()->getSchema())) {
				$tableName .= $join->getTable()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getTable()->getName();
			$sql .= $tableName . ' ' . $joinAlias;
		} else {
			$tableName = '';

			if (!empty($table->getSchema())) {
				$tableName .= $table->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $table->getName();
			$sql .= $tableName . ' ' . $alias;
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $joinAlias . '.';

		$_joins = $table->getJoins('reference', $join->getTable()->getClass());
		$belongsTo = null;

		foreach ($_joins as $_join) {
			if ($_join->getType() === 'belongsTo') {
				$belongsTo = $_join;
				break;
			}
		}

		if (!empty($belongsTo)) {
			$sql .= $belongsTo->getName() . ' = ';
		} else {
			$sql .= $table->getName() . '_';
			$sql .= $table->getId()->getName() . ' = ';
		}

		$sql .= $alias . '.' . $table->getId()->getName();

		return $sql;
	}

	private function resolveJoinHasMany(Table $table, String $alias, Join $join, String $joinAlias, String $joinType) : String
	{
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (!array_key_exists($alias . ':' . $table->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($table->getSchema())) {
				$tableName .= $table->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $table->getName();
			$sql .= $tableName . ' ' . $alias;
		} else {
			$tableName = '';

			if (!empty($join->getTable()->getSchema())) {
				$tableName .= $join->getTable()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getTable()->getName();
			$sql .= $tableName . ' ' . $joinAlias;
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $joinAlias . '.';
		$sql .= $join->getTable()->getId()->getName() . ' = ';
		$sql .= $alias . '.';

		$_joins = $table->getJoins('reference', $join->getTable()->getClass());
		$belongsTo = null;

		foreach ($_joins as $_join) {
			if ($_join->getType() === 'belongsTo') {
				$belongsTo = $_join;
				break;
			}
		}

		if (!empty($belongsTo)) {
			$sql .= $belongsTo->getName();
		} else {
			$sql .= $join->getTable()->getName() . '_';
			$sql .= $join->getTable()->getId()->getName();
		}

		return $sql;
	}

	private function resolveJoinManyToMany(Table $table, String $alias, Join $join, String $joinAlias, String $joinType) : String
	{
		if ($join->getMappedBy()) {
			$tempJoin = $table->getJoins('property', $join->getMappedBy());
			$table = $join->getTable();
			$join = $tempJoin[0];
			$tempAlias = $alias;
			$alias = $joinAlias;
			$joinAlias = $tempAlias;
		}

		$sql = "\n\t" . $joinType . ' JOIN ';

		$joinTableName = '';

		if (!empty($join->getJoinTable()->getSchema())) {
			$joinTableName .= $join->getJoinTable()->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$joinTableName .= $this->connection->getDefaultSchema() . '.';
		}

		$joinTableName .= $join->getJoinTable()->getName();
		$joinTableAlias = $alias . '_' . $joinAlias;

		if (!array_key_exists($joinAlias . ':' . $join->getTable()->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($join->getTable()->getSchema())) {
				$tableName .= $join->getTable()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getTable()->getName();

			$sql .= $joinTableName . ' ' . $joinTableAlias;
			$sql .= "\n\t\t" . 'ON ';
			$sql .= $joinTableAlias . '.' . $join->getJoinTable()->getInverseName() . ' = ';
			$sql .= $alias . '.' . $table->getId()->getName();

			$sql .= "\n\t" . $joinType . ' JOIN ';
			$sql .= $tableName . ' ' . $joinAlias;
		} else {
			$sql .= $joinTableName . ' ' . $joinTableAlias;
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $joinAlias . '.';
		$sql .= $join->getTable()->getId()->getName() . ' = ';
		$sql .= $joinTableAlias . '.' . $join->getJoinTable()->getJoinName();

		if (!array_key_exists($alias . ':' . $table->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($table->getSchema())) {
				$tableName .= $table->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $table->getName();

			$sql .= "\n\t" . $joinType . ' JOIN ' . $tableName . ' ' . $alias;
			$sql .= "\n\t\t" . 'ON ';
			$sql .= $alias . '.' . $table->getId()->getName() . ' = ';
			$sql .= $joinTableAlias . '.' . $join->getJoinTable()->getInverseName();
		}

		return $sql;
	}

	private function resolveJoinBelongsTo(Table $table, String $alias, Join $join, String $joinAlias, String $joinType) : String
	{
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (!array_key_exists($alias . ':' . $table->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($table->getSchema())) {
				$tableName .= $table->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $table->getName();
			$sql .= $tableName . ' ' . $alias;
		} else {
			$tableName = '';

			if (!empty($join->getTable()->getSchema())) {
				$tableName .= $join->getTable()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getTable()->getName();
			$sql .= $tableName . ' ' . $joinAlias;
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $alias . '.';
		$sql .= $table->getId()->getName() . ' = ';
		$sql .= $joinAlias . '.';
		$sql .= $join->getName();

		return $sql;
	}

}
