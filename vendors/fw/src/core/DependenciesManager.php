<?php

namespace FW\Core;

class DependenciesManager
{

	private static $instance;

	private $instances;

	protected function __construct()
	{
		$this->instances = [];
	}

	public static function getInstance() : self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register($class)
	{
		$reflection = new \ReflectionClass($class);

		$dependencies = [];

		if (!empty($reflection->getInterfaceNames())) {
			$names[] = $reflection->getInterfaceNames()[0];
		}

		$names[] = $reflection->getName();
		$constructor = $reflection->getConstructor();

		if ($constructor) {
			foreach ($constructor->getParameters() as $parameter) {
				$type = $defaultValue = null;

				if ($parameter->hasType()) {
					$type = (object) [
						'name' => $parameter->getType()->__toString(),
						'injectable' => !$parameter->getType()->isBuiltIn() && !$parameter->isArray()
					];
				}

				if (!$parameter->isDefaultValueAvailable() && ($type && !$type->injectable)) {
					throw new \Exception('The parameter "' . $parameter->getName() . '" of the constructor of the class "' . $class . '" must have a default value');
				} elseif($parameter->isDefaultValueAvailable()) {
					$defaultValue = $parameter->getDefaultValue();
				}

				$dependency = [
					'position' => $parameter->getPosition(),
					'name' => $parameter->getName(),
					'defaultValue' => $defaultValue,
					'type' => $type,
				];

				$dependencies[] = (object) $dependency;
			}
		}

		foreach ($names as $name) {
			if (array_key_exists($name, $this->instances)) {
				throw new \Exception('An instance for the interface "' . $name . '" already exists');
			}

			$this->instances[$name] = (object) [
				'name' => $reflection->getName(),
				'instance' => null,
				'dependencies' => $dependencies
			];
		}
	}

	public function value($name, $value)
	{
		if (array_key_exists($name, $this->instances)) {
			throw new \Exception('A value with the name "' . $name . '" already exists');
		}

		$this->instances[$name] = (object) [
			'name' => null,
			'instance' => $value,
			'dependencies' => null
		];
	}

	public function resolve($class)
	{
		if (!array_key_exists($class, $this->instances)) {
			throw new \Exception('An instance for "' . $class . '" was not founded');
		}

		$map = $this->instances[$class];

		if (!is_null($map->instance)) {
			return $map->instance;
		}

		$dependencies = [];

		foreach ($map->dependencies as $dependency) {
			if ($dependency->type && $dependency->type->injectable) {
				$dependencies[$dependency->position] = $this->resolve($dependency->type->name);
			} else {
				$dependencies[$dependency->position] = $dependency->defaultValue;
			}
		}

		$instance = new $map->name(...$dependencies);

		$this->instances[$class]->instance = $instance;

		return $instance;
	}

}
