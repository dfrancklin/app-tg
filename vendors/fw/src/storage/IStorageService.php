<?php

namespace FW\Storage;

use FW\Storage\Strategies\ISessionStorageStrategy;
use FW\Storage\Strategies\ICookieStorageStrategy;
use FW\Storage\Strategies\IFileStorageStrategy;

interface IStorageService
{

	function session() : ISessionStorageStrategy;

	function cookie() : ICookieStorageStrategy;

	function file() : IFileStorageStrategy;

}