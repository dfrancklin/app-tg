<?php

namespace FW\Storage\Strategies;

interface IFileStorageStrategy extends IStorageStrategy
{

	function append($name, $value);

}