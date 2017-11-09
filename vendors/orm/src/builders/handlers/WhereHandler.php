<?php

namespace ORM\Builders\Handlers;

use ORM\Mappers\Column;
use ORM\Mappers\Table;

use ORM\Builders\Criteria;
use ORM\Builders\Handlers\OperatorHandler;

trait WhereHandler
{

	private $whereConditions;

	private $values;

	public function where(String $property) : Criteria
	{
		$this->chain = OperatorHandler::$WHERE;

		$criteria = new Criteria($this);

		array_push($this->whereConditions, [$property, $criteria]);

		$this->and();

		return $criteria;
	}

	private function resolveWhere() : String
	{
		if ($this->whereConditions === null) {
			return '';
		}

		$sql = '';

		if (count($this->whereConditions)) {
			$sql .= "\n" . 'WHERE ';
		}

		foreach($this->whereConditions as $key => $condition) {
			if (($condition[2] === 'or' && $key === 0) ||
					($condition[2] === 'or' && $key > 0 &&
						$this->whereConditions[$key - 1][2] !== 'or')) {
				$sql .= '(';
			}

			$sql .= $this->resolveWhereCondition(...$condition);

			if ($condition[2] !== 'or' && $key > 0 &&
					$this->whereConditions[$key - 1][2] === 'or') {
				$sql .= ')';
			}

			if ($key < count($this->whereConditions) - 1) {
				$sql .= ' ' . $condition[2] . ' ';

				if ($condition[2] !== 'or') {
					$sql .= "\n\t\t";
				}
			}
		}

		return $sql;
	}

	private function resolveWhereCondition(String $property, Criteria $criteria) : String
	{
		$sql = '';

		list($prop, $table, $column) = $this->processProperty($property);
		$values = $this->processValues($criteria->getValues(), $column);
		$alias = str_replace('.', '_', $property);
		$args = [$prop];

		if (array_key_exists($alias, $this->values) ||
				array_key_exists($alias . '_1', $this->values)) {
			$count = 2;

			while(array_key_exists($alias . $count, $this->values) ||
					array_key_exists($alias . $count . '_1', $this->values)) {
				$count++;
			}

			$alias = $alias . $count;
		}

		if ($criteria->getAction() === Criteria::BETWEEN ||
				$criteria->getAction() === Criteria::NOT_BETWEEN) {
			$this->values[$alias . '_1'] = $values[0];
			$this->values[$alias . '_2'] = $values[1];
			array_push($args, ':' . $alias . '_1', ':' . $alias . '_2');
		} elseif (count($values)) {
			$this->values[$alias] = $values[0];
			array_push($args, ':' . $alias);
		}

		$template = $criteria->getTemplate();

		$sql .= vsprintf($template, $args);

		return $sql;
	}

	private function processProperty(String $property) : Array
	{
		$alias = null;

		if ($index = strpos($property, '.')) {
			$alias = substr($property, 0, $index);
			$property = substr($property, $index + 1);
		}

		if (!($table = $this->findTable($alias))) {
			throw new \Exception('Invalid alias "' . $alias . '"');
		}

		list($table) = $table;

		if (!($column = $table->findColumn($property))) {
			throw new \Exception('Invalid property "' . $property . '"');
		}

		$property = $alias . '.' . $column->getName();

		return [$property, $table, $column];
	}

	private function processValues(Array $values, $column) : Array
	{
		$type = '';

		if ($column instanceof Column) {
			$type = $column->getType();
		} else {
			$class = $column->getReference();
			$reference = $this->orm->getTable($class);
			$type = $reference->getId()->getType();
		}

		$processedValues = [];

		foreach ($values as $value) {
			if ($value instanceof \DateTime) {
				$format = $this->connection->getDriver()->FORMATS[$type] ?? 'Y-m-d';
				array_push($processedValues, $value->format($format));
			} else {
				array_push($processedValues, $value);
			}
		}

		return $processedValues;
	}

	private function findTable(String $alias)
	{
		$table = null;

		if (!array_key_exists($alias, $this->tablesByAlias)) {
			throw new \Exception('Invalid alias "' . $alias . '"');
		}

		return $this->tablesByAlias[$alias];
	}

}
