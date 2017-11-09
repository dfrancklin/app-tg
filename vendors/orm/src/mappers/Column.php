<?php

namespace ORM\Mappers;

class Column
{

	private $table;

	private $property;

	private $id;

	private $generated;

	private $name;

	private $type;

	private $length;

	private $scale;

	private $precision;

	private $nullable;

	private $unique;

	public function __construct()
	{
		$this->id = false;
		$this->generated = false;
		$this->type = 'string';
		$this->length = 255;
		$this->scale = 0;
		$this->precision = 0;
		$this->nullable = true;
		$this->unique = false;
	}

	public function getTable() : Table
	{
		return $this->table;
	}

	public function setTable(Table $table)
	{
		$this->table = $table;
	}

	public function getProperty() : String
	{
		return $this->property;
	}

	public function setProperty(String $property)
	{
		$this->property = $property;
	}

	public function isId() : bool
	{
		return $this->id;
	}

	public function setId(bool $id)
	{
		$this->id = $id;
	}

	public function isGenerated() : bool
	{
		return $this->generated;
	}

	public function setGenerated(bool $generated)
	{
		$this->generated = $generated;
	}

	public function getName() : String
	{
		return $this->name;
	}

	public function setName(String $name)
	{
		$this->name = $name;
	}

	public function getType() : String
	{
		return $this->type;
	}

	public function setType(String $type)
	{
		$this->type = $type;
	}

	public function getLength() : int
	{
		return $this->length;
	}

	public function setLength(int $length)
	{
		$this->length = $length;
	}

	public function getScale() : int
	{
		return $this->scale;
	}

	public function setScale(int $scale)
	{
		$this->scale = $scale;
	}

	public function getPrecision() : int
	{
		return $this->precision;
	}

	public function setPrecision(int $precision)
	{
		$this->precision = $precision;
	}

	public function isNullable() : bool
	{
		return $this->nullable;
	}

	public function setNullable(bool $nullable)
	{
		$this->nullable = $nullable;
	}

	public function isUnique() : bool
	{
		return $this->unique;
	}

	public function setUnique(bool $unique)
	{
		$this->unique = $unique;
	}

}
