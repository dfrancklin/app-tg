<?php

namespace ORM\Mappers;

class Join
{

	private $table;

	private $reference;

	private $name;

	private $property;

	private $mappedBy;

	private $joinTable;

	private $type;

	private $cascade;

	private $optional;

	public function __construct()
	{
		$this->cascade = [];
		$this->optional = false;
	}

	public function getTable() : Table
	{
		return $this->table;
	}

	public function setTable(Table $table)
	{
		$this->table = $table;
	}

	public function getClass() : String
	{
		return $this->class;
	}

	public function setClass(String $class)
	{
		$this->class = $class;
	}

	public function getReference() : String
	{
		return $this->reference;
	}

	public function setReference(String $reference)
	{
		$this->reference = $reference;
	}

	public function getName() : ?String
	{
		return $this->name;
	}

	public function setName(String $name)
	{
		$this->name = $name;
	}

	public function getProperty() : String
	{
		return $this->property;
	}

	public function setProperty(String $property)
	{
		$this->property = $property;
	}

	public function getMappedBy() : ?String
	{
		return $this->mappedBy;
	}

	public function setMappedBy(String $mappedBy)
	{
		$this->mappedBy = $mappedBy;
	}

	public function getJoinTable() : JoinTable {
		return $this->joinTable;
	}

	public function setJoinTable(JoinTable $joinTable)
	{
		$this->joinTable = $joinTable;
	}

	public function getType() : String
	{
		return $this->type;
	}

	public function setType(String $type)
	{
		$this->type = $type;
	}

	public function getCascade() : Array
	{
		return $this->cascade;
	}

	public function setCascade(Array $cascade)
	{
		$this->cascade = $cascade;
	}

	public function isOptional() : bool{
		return $this->optional;
	}

	public function setOptional(bool $optional)
	{
		$this->optional = $optional;
	}

}
