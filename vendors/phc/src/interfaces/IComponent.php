<?php

namespace PHC\Interfaces;

interface IComponent
{

	function render(bool $print = false);

	function __get(String $attr);

	function __set(String $attr, $value);

	function __toString();

}
