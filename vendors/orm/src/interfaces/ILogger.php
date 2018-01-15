<?php

namespace ORM\Interfaces;

Interface ILogger
{

	function debug(String $message, String $class);

	function info(String $message, String $class);

	function warning(String $message, String $class);

	function error(String $message, String $class);

}
