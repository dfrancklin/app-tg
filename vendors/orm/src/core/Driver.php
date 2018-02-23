<?php

namespace ORM\Core;

abstract class Driver
{

	public $GENERATE_ID_TYPE;

	public $GENERATE_ID_ATTR;

	public $GENERATE_ID_QUERY;

	public $SEQUENCE_NAME = 'orm_sequence';

	public $IGNORE_ID_DATA_TYPE = false;

	public $FK_ENABLE = true;

	public $PAGE_TEMPLATE;

	public $TOP_TEMPLATE;

	public $DATA_TYPES = [];

	public $FORMATS = [
		'date' => 'Y-m-d',
		'time' => 'H:i:s',
		'datetime' => 'Y-m-d H:i:s'
	];

	public function convertToType($value, String $type)
	{
		$method = 'convertTo' . ucfirst($type);

		if (method_exists($this, $method)) {
			return $this->$method($value);
		}

		return $value;
	}

	public function convertFromType($value, String $type)
	{
		$method = 'convertFrom' . ucfirst($type);


		if (method_exists($this, $method)) {
			return $this->$method($value);
		}

		return $value;
	}

	public function convertToInt($value) : int
	{
		return (int) $value;
	}

	public function convertToFloat($value) : float
	{
		return (float) $value;
	}

	public function convertToDate($value) : \DateTime
	{
		return new \DateTime($value);
	}

	public function convertFromDate($value) : String
	{
		if ($value instanceof \DateTime) {
			return $value->format(self::FORMATS['date']);
		}

		return $value;
	}

	public function convertToTime($value) : \DateTime
	{
		return $this->convertToDate($value);
	}

	public function convertFromTime($value) : String
	{
		if ($value instanceof \DateTime) {
			return $value->format(self::FORMATS['time']);
		}

		return $value;
	}

	public function convertToDatetime($value) : \DateTime
	{
		return $this->convertToDate($value);
	}

	public function convertFromDateTime($value) : String
	{
		if ($value instanceof \DateTime) {
			return $value->format(self::FORMATS['datetime']);
		}

		return $value;
	}

	public function convertToBool($value) : bool
	{
		return in_array($value, [1, '1', 'true', 'TRUE', 't', 'T'], true);
	}

	public function convertFromBool($value) : int
	{
		return $value ? 1 : 0;
	}

}
