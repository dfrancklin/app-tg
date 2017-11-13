<?php

namespace FW\Storage\Strategies;

interface IStorageStrategy
{

	function save($name, $value);

	function get($name);

	function remove($name);

	function list();

	function clear();

}