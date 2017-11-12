<?php

namespace FW\Core;

class Config
{

	private static $instance;

	private $data;

	protected function __construct()
	{
		$this->data = [];
	}

	public static function getInstance() : self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function get($name)
	{
		if (!array_key_exists($name, $this->data)) {
			throw new \Exception('Data with name "' . $name . '" not defined');
		}

		return $this->data[$name];
	}

}
