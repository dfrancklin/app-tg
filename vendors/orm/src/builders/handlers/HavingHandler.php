<?php

namespace ORM\Builders\Handlers;

use ORM\Builders\Aggregate;
use ORM\Builders\Criteria;

trait HavingHandler
{

	private $havingConditions;

	public function having()
	{
		$this->chain = Operator::$HAVING;

		$aggregate = new Aggregate($this);

		array_push($this->havingConditions, [$aggregate]);

		return $aggregate;
	}

	private function resolveHaving()
	{
		if (empty($this->havingConditions)) {
			return;
		}

		$sql = '';

		if (count($this->havingConditions)) {
			$sql = "\nHAVING ";
		}

		$this->havingConditions[count($this->havingConditions) - 1][1] = 'and';

		foreach($this->havingConditions as $key => $condition) {
			if (($condition[1] === 'or' && $key === 0) ||
					($condition[1] === 'or' && $key > 0 &&
						$this->havingConditions[$key - 1][1] !== 'or')) {
				$sql .= '(';
			}

			$sql .= $this->resolveHavingCondition(...$condition);

			if ($condition[1] !== 'or' && $key > 0 &&
					$this->havingConditions[$key - 1][1] === 'or') {
				$sql .= ')';
			}

			if ($key < count($this->havingConditions) - 1) {
				$sql .= ' ' . $condition[1] . ' ';

				if ($condition[1] !== 'or') {
					$sql .= "\n\t";
				}
			}
		}

		return $sql;
	}


	private function resolveHavingCondition($condition) : String
	{
		$sql = '';

		list($property, $criteria) = $condition->getCriteria();
		list($prop, , $column) = $this->processProperty($property);
		$values = $this->processValues($criteria->getValues(), $column);
		$alias = str_replace('.', '_', $property);
		$args = [$condition->getTemplate()];

		if (array_key_exists($alias, $this->values) ||
				array_key_exists($alias . '_1', $this->values)) {
			$count = 2;

			while(array_key_exists($alias . $count, $this->values) ||
					array_key_exists($alias . $count . '_1', $this->values)) {
				$count++;
			}

			$alias = $alias . $count;
		}

		if ($criteria->getAction() === Criteria::BETWEEN) {
			$this->values[$alias . '_1'] = $values[0];
			$this->values[$alias . '_2'] = $values[1];
			array_push($args, ':' . $alias . '_1', ':' . $alias . '_2');
		} elseif (count($values)) {
			$this->values[$alias] = $values[0];
			array_push($args, ':' . $alias);
		}

		$template = vsprintf($criteria->getTemplate(), $args);
		$sql .= vsprintf($template, $prop);

		return $sql;
	}

}
