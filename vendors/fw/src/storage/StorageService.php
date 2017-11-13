<?php

namespace FW\Storage;

use FW\Storage\Strategies\SessionStorageStrategy;
use FW\Storage\Strategies\CookieStorageStrategy;
use FW\Storage\Strategies\FileStorageStrategy;

use FW\Storage\Strategies\ISessionStorageStrategy;
use FW\Storage\Strategies\ICookieStorageStrategy;
use FW\Storage\Strategies\IFileStorageStrategy;

/**
 * @Service;
 */
class StorageService implements IStorageService
{

	private $session;

	private $cookie;

	private $file;

	public function session() : ISessionStorageStrategy
	{
		if (empty($this->session)) {
			$this->session = new SessionStorageStrategy;
		}

		return $this->session;
	}

	public function cookie() : ICookieStorageStrategy
	{
		if (empty($this->cookie)) {
			$this->cookie = new CookieStorageStrategy;
		}

		return $this->cookie;
	}

	public function file() : IFileStorageStrategy
	{
		if (empty($this->file)) {
			$this->file = new FileStorageStrategy;
		}

		return $this->file;
	}

}