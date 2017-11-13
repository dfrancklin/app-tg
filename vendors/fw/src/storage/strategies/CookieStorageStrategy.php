<?php

namespace FW\Storage\Strategies;

class CookieStorageStrategy implements ICookieStorageStrategy
{

	public function __construct($domain='storage')
	{
		$this->domain = $domain;
	}

	public function save($name, $value)
	{

	}

	public function get($name)
	{

	}

	public function remove($name)
	{

	}

	public function list()
	{

	}

	public function clear()
	{

	}

}