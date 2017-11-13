<?php

namespace FW\Storage\Strategies;

class FileStorageStrategy implements IFileStorageStrategy
{

	public function __construct($domain)
	{
		$this->domain = $domain;
	}

	public function save($name, $value)
	{

	}

	public function append($name, $value)
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
