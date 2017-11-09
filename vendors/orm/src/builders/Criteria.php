<?php

namespace ORM\Builders;

use ORM\Builders\Query;

use ORM\Builders\Handlers\OperatorHandler;

class Criteria
{

	const EQUALS = 'equals';

	const NOT_EQUALS = 'notEquals';

	const IS_NULL = 'isNull';

	const IS_NOT_NULL = 'isNotNull';

	const BETWEEN = 'between';

	const NOT_BETWEEN = 'notBetween';

	const GREATER_THAN = 'greaterThan';

	const GREATER_OR_EQUALS_THAN = 'greaterOrEqualsThan';

	const LESS_THAN = 'lessThan';

	const LESS_OR_EQUALS_THAN = 'lessOrEqualsThan';

	const IN = 'in';

	const NOT_IN = 'notIn';

	const LIKE = 'like';

	const NOT_LIKE = 'notLike';

	private static $templates = [
		self::EQUALS => '%s = %s',
		self::NOT_EQUALS => '%s != %s',
		self::IS_NULL => '%s is null',
		self::IS_NOT_NULL => '%s is not null',
		self::BETWEEN => '(%s between %s and %s)',
		self::NOT_BETWEEN => '(%s not between %s and %s)',
		self::GREATER_THAN => '%s > %s',
		self::GREATER_OR_EQUALS_THAN => '%s >= %s',
		self::LESS_THAN => '%s < %s',
		self::LESS_OR_EQUALS_THAN => '%s <= %s',
		self::IN => '%s in (%s)',
		self::NOT_IN => '%s not in (%s)',
		self::LIKE => '%s like %s',
		self::NOT_LIKE => '%s not like %s',
	];

	private static $alias = [
		'eq' => self::EQUALS,
		'neq' => self::NOT_EQUALS,
		'isn' => self::IS_NULL,
		'isnn' => self::IS_NOT_NULL,
		'bt' => self::BETWEEN,
		'nbt' => self::NOT_BETWEEN,
		'gt' => self::GREATER_THAN,
		'goet' => self::GREATER_OR_EQUALS_THAN,
		'lt' => self::LESS_THAN,
		'loet' => self::LESS_OR_EQUALS_THAN,
		'lk' => self::LIKE,
		'nlk' => self::NOT_LIKE,
	];

	private static $shortcuts = [
		'contains' => [
			'like',
			'%${value}%'
		],
		'notContains' => [
			'notLike',
			'%${value}%'
		],
		'beginsWith' => [
			'like',
			'${value}%'
		],
		'notBeginsWith' => [
			'notLike',
			'${value}%'
		],
		'endsWith' => [
			'like',
			'%${value}'
		],
		'notEndsWith' => [
			'notLike',
			'%${value}'
		],
	];

	private static $shortcutsAlias = [
		'ctn' => 'contains',
		'nctn' => 'notContains',
		'bwt' => 'beginsWith',
		'nbwt' => 'notBeginsWith',
		'ewt' => 'endsWith',
		'newt' => 'notEndsWith',
	];

	private static $parameters = [
		self::BETWEEN => 2,
		self::NOT_BETWEEN => 2,
		self::IN => 'at least one',
		self::NOT_IN => 'at least one',
		self::IS_NULL => 0,
		self::IS_NOT_NULL => 0,
	];

	private static $excluded = [
		self::IN,
		self::NOT_IN,
		self::LIKE,
		self::NOT_LIKE
	];

	private $builder;

	private $action;

	private $values;

	public function __construct($builder)
	{
		$this->builder = $builder;
		$this->values = [];
	}

	public function __call($method, $parameters) : Query
	{
		$this->methodExists($method);

		if (array_key_exists($method, self::$shortcutsAlias)) {
			$method = self::$shortcutsAlias[$method];
		}

		if (array_key_exists($method, self::$shortcuts)) {
			list($call, $parameter) = self::$shortcuts[$method];
			$parameter = str_replace('${value}', $parameters[0], $parameter);

			return $this->$call($parameter);
		}

		if (array_key_exists($method, self::$alias)) {
			$method = self::$alias[$method];
		}

		$this->methodParameters($method, $parameters);

		$this->action = $method;

		if (count($parameters)) {
			if ($method === self::IN || $method === self::NOT_IN) {
				array_push($this->values, $parameters);
			} else {
				array_push($this->values, ...$parameters);
			}
		}

		return $this->builder;
	}

	public function getAction() : String
	{
		return $this->action;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function getTemplate() : String
	{
		if (!array_key_exists($this->action, self::$templates)) {
			throw new \Exception('Action "' . $this->action . '" does not exists.');
		}

		return self::$templates[$this->action];
	}

	private function methodExists(String $method)
	{
		if ((!array_key_exists($method, self::$templates) && !array_key_exists($method, self::$alias) &&
				!array_key_exists($method, self::$shortcuts) && !array_key_exists($method, self::$shortcutsAlias)) ||
			($this->builder->getChain() === OperatorHandler::$HAVING && in_array($method, self::$excluded))
		) {
			throw new \Exception('Invalid method "' . $method . '" of the "' . __CLASS__ . '" class');
		}
	}

	private function methodParameters(String $method, $parameters)
	{
		if ((array_key_exists($method, self::$parameters) &&
				((self::$parameters[$method] === 'at least one' && count($parameters) === 0) ||
					(self::$parameters[$method] !== 'at least one' && count($parameters) !== self::$parameters[$method]))) ||
			(!array_key_exists($method, self::$parameters) && count($parameters) !== 1)
		) {
			$quantity = isset(self::$parameters[$method]) ? self::$parameters[$method] : 1;

			$message = 'The method "' . $method . '" expects ' . $quantity;
			$message .= ' argument' . (is_numeric($quantity) && $quantity == 0 || $quantity > 1 ? 's' : '');
			$message .= ' and ' . count($parameters) . ' was provided.';

			throw new \Exception($message);
		}
	}

}
