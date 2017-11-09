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

}
