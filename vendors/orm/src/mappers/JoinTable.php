<?php

namespace ORM\Mappers;

class JoinTable
{

	private $name;

	private $schema;

	private $joinName;

	private $inverseName;

	public function getName() : ?String
	{
		return $this->name;
	}

	public function setName(String $name)
	{
		$this->name = $name;
	}

	public function getSchema() : ?String
	{
		return $this->schema;
	}

	public function setSchema(String $schema)
	{
		$this->schema = $schema;
	}

	public function getJoinName() : ?String
	{
		return $this->joinName;
	}

	public function setJoinName(String $joinName)
	{
		$this->joinName = $joinName;
	}

	public function getInverseName() : ?String
	{
		return $this->inverseName;
	}

	public function setInverseName(String $inverseName)
	{
		$this->inverseName = $inverseName;
	}

}
