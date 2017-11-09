<?php

namespace ORM\Constants;

class OrmExpressions
{

	const ANY = '\\\\@A-Za-z0-9=,_\/\s\(\)';
	const ANY_WITH_BRACES = self::ANY . '\{\}';

	// Annotations
	const ORM = '/@ORM\/[' . self::ANY_WITH_BRACES . ']+/i';
	const ENTITY = '/Entity/i';
	const ID = '/@ORM\/(Id)/i';
	const GENERATED = '/Generated/i';
	const TABLE = '/Table\([' . self::ANY . ']+\)/i';
	const COLUMN = '/Column\([' . self::ANY . ']+\)/i';
	const TRANSIENT = '/Transient/i';

	// Joins
	const HAS_ONE = '/HasOne\([^\)]+\)/i';
	const HAS_MANY = '/HasMany\([^\)]+\)/i';
	const MANY_TO_MANY = '/ManyToMany\([^\)]+\)/i';
	const BELONGS_TO = '/BelongsTo\([^\)]+\)/i';
	const JOIN_COLUMN = '/JoinColumn\([' . self::ANY . ']+\)/i';
	const JOIN_TABLE = '/JoinTable\(.+\)/i';

	// Attributes of annotations
	const NAME = '/name[\s]?=[\s]?(\w+)/i';
	const TYPE = '/type[\s]?=[\s]?(\w+)/i';
	const LENGTH = '/length[\s]?=[\s]?(\d+)/i';
	const SCALE = '/scale[\s]?=[\s]?(\d+)/i';
	const PRECISION = '/precision[\s]?=[\s]?(\d+)/i';
	const NULLABLE = '/nullable[\s]?=[\s]?(\w+)/i';
	const UNIQUE = '/unique[\s]?=[\s]?(\w+)/i';
	const SCHEMA = '/schema[\s]?=[\s]?(\w+)/i';
	const MUTABLE = '/mutable[\s]?=[\s]?(\w+)/i';

	// Attributes from joins
	const TABLE_NAME = '/tableName[\s]?=[\s]?(\w+)/i';
	const JOIN = '/join[\s]?=[\s]?\{([' . self::ANY . ']*)\}/i';
	const INVERSE = '/inverse[\s]?=[\s]?\{([' . self::ANY . ']*)\}/i';
	const MAPPED_BY = '/mappedBy[\s]?=[\s]?(\w+)/i';
	const CLASS_NAME = '/class[\s]?=[\s]?([\\\\\w]+)/i';
	const CASCADE = '/cascade[\s]?=[\s]?\{([A-Za-z,\s]+)\}/i';
	const OPTIONAL = '/optional[\s]?=[\s]?(\w+)/i';

}
