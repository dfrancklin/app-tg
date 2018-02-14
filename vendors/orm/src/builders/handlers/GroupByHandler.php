<?php

namespace ORM\Builders\Handlers;

trait GroupByHandler
{

	private $groups;

	public function groupBy(...$groups)
	{
		$this->groups = $groups;

		return $this;
	}

	private function resolveGroupBy() : String
	{
		$resolved = [];
		$sql = '';

		if (!empty($this->groups)) {
			$sql = "\n" . 'GROUP BY ';
		}

		foreach ($this->groups as $property) {
			list($prop) = $this->processProperty($property);
			array_push($resolved, $prop);
			array_push($this->columns, $prop);
		}


		return $sql . join(', ', $resolved);
	}

}
