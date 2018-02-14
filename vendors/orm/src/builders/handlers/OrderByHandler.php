<?php

namespace ORM\Builders\Handlers;

use ORM\Constants\OrderTypes;

trait OrderByHandler
{

	private $orders;

	public function orderBy($order, $dir = null)
	{
		if (empty($dir) || !in_array($dir, OrderTypes::TYPES)) {
			$dir = OrderTypes::ASC;
		}

		array_push($this->orders, [$order, $dir]);

		return $this;
	}

	private function resolveOrderBy() : String
	{
		$resolved = [];
		$sql = '';

		if (!empty($this->orders)) {
			$sql = "\n" . 'ORDER BY ';
		}

		foreach ($this->orders as $order) {
			list($property, $dir) = $order;

			try {
				list($prop) = $this->processProperty($property);
			} catch (\Throwable $e) {
				$prop = $property;
			}

			array_push($resolved, sprintf('%s %s', $prop, $dir));
		}

		return $sql . join(', ', $resolved);
	}

}
