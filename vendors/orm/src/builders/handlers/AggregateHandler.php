<?php

namespace ORM\Builders\Handlers;

use ORM\Builders\Criteria;

trait AggregateHandler
{

	public static $QUERY = 'query';

	public static $HAVING = 'having';

	private static $AVG = 'avg';

	private static $SUM = 'sum';

	private static $MIN = 'min';

	private static $MAX = 'max';

	private static $COUNT = 'count';

	private static $templates = [];

	private static $initialized;

	private $builder;

	private $action;

	private $criteria;

	private $context;

	private $aggregations;

	public function __construct($builder, $context = null)
	{
		$this->builder = $builder;
		$this->context = $context;
		$this->criteria = [];

		if (!$context) {
			$this->context = self::$HAVING;
		}
	}

	private static function init()
	{
		self::$templates = [
			self::$AVG => self::$AVG . '(%s)',
			self::$SUM => self::$SUM . '(%s)',
			self::$MIN => self::$MIN . '(%s)',
			self::$MAX => self::$MAX . '(%s)',
			self::$COUNT => self::$COUNT . '(%s)',
		];
	}

	public function __call($method, $parameters)
	{
		if (!self::$initialized) {
			self::init();
		}

		if (in_array($method, ['getAction', 'getCriteria', 'getTemplate']) &&
				$this->context === self::$HAVING) {
			return $this->$method();
		}

		if (!array_key_exists($method, self::$templates)) {
			throw new \Exception('Invalid method "' . $method . '" of the "' . __CLASS__ . '" class');
		}

		if ($this->context === self::$HAVING) {
			return $this->handleHaving($method, $parameters);
		} else {
			return $this->handleQuery($method, ...$parameters);
		}
	}

	private function handleHaving($method, $parameters)
	{
		if (count($parameters) !== 1) {
			throw new \Exception('The method "' . $method . '" expects 1 argument and ' . count($parameters) . ' was provided.');
		}

		$criteria = new Criteria($this->builder);

		$this->action = $method;
		$this->criteria = [$parameters[0], $criteria];

		return $criteria;
	}

	private function handleQuery($method, $property, $alias = null)
	{
		array_push($this->aggregations, [$property, $method, $alias]);

		return $this;
	}

	private function resolveAggregations() : String
	{
		if ($this->context === self::$HAVING) {
			throw new \Exception('Invalid method "resolveAggregations" of the "' . __CLASS__ . '" class');
		}

		$resolved = [];

		foreach ($this->aggregations as $aggregation) {
			list($property, $action, $alias) = $aggregation;
			list($prop) = $this->processProperty($property);
			$template = self::$templates[$action];
			$value = sprintf($template, $prop);

			if (!empty($alias)) {
				$value .= ' as ' . $alias;
			}

			array_push($resolved, $value);
			$i = array_push($this->columns, $value);
		}

		return join(', ', $resolved);
	}

	private function getAction() : String
	{
		return $this->action;
	}

	private function getCriteria() : Criteria
	{
		return $this->criteria;
	}

	private function getTemplate() : String
	{
		if (!array_key_exists($this->action, self::$templates)) {
			throw new \Exception('Action "' . $this->action . '" does not exists.');
		}

		return self::$templates[$this->action];
	}

}

