<?php

namespace ORM\Helpers;

use ORM\Constants\OrmExpressions;

class ExpressionResolver
{

	public static function get(String $expression, ?String $comment, bool $all = false) : ?String
	{
		$expression = preg_replace("/[A-Z]/", "_$0", $expression);
		$expression = strtoupper($expression);
		$constant = OrmExpressions::class . '::' . $expression;

		if (!defined($constant)) {
			throw new \Exception('The constant "' . $constant . '" does not exist');
		}

		$expression = constant($constant);
		$comment = self::stripChars($comment);

		if ($all) {
			return self::all($expression, $comment);
		} else {
			return self::match($expression, $comment);
		}
	}

	private static function all(String $expression, String $comment) : ?String
	{
		preg_match_all($expression, $comment, $matches);

		if (isset($matches[0])) {
			return join('', $matches[0]);
		} else {
			return null;
		}
	}

	private static function match(String $expression, String $comment) : ?String
	{
		preg_match($expression, $comment, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		} elseif (isset($matches[0])) {
			return $matches[0];
		} else {
			return null;
		}
	}

	public static function stripChars(?String $comment) : ?String
	{
		$comment = preg_replace("/\n?@ORM/i", "|@ORM", $comment, -1, $count);
		$comment = preg_replace("/(\/\*|\*\/|\*|\s+)*/i", "", $comment);
		$comment = trim(preg_replace("/\|/i", "\n", $comment));

		return $comment;
	}

}
