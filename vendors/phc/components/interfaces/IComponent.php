<?php

namespace App\Interfaces;

interface IComponent {

	function render(bool $print = false);

	function __get(string $attr);

	function __set(string $attr, $value);

	function __toString();

}
