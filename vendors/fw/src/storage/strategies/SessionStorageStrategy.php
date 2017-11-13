<?php

namespace FW\Storage\Strategies;

use FW\Core\Config;

class SessionStorageStrategy implements ISessionStorageStrategy
{

	public function __construct($domain='storage')
	{
		$config = Config::getInstance();
		$this->appId = $config->get('app-id');
		$this->domain = $domain;
	}

	public function save($name, $value)
	{
		if (!$this->isSessionOpen()) {
			throw new \Exception('The session must be started');
		}

		$_SESSION[$this->appId][$this->domain][$name] = $value;
	}

	public function get($name)
	{
		if (!$this->isSessionOpen()) {
			throw new \Exception('The session must be started');
		}

		if (!empty($_SESSION[$this->appId][$this->domain][$name])) {
			return $_SESSION[$this->appId][$this->domain][$name];
		}

		return null;
	}

	public function remove($name)
	{
		if (!$this->isSessionOpen()) {
			throw new \Exception('The session must be started');
		}

		if (!empty($_SESSION[$this->appId][$this->domain][$name])) {
			unset($_SESSION[$this->appId][$this->domain][$name]);
		}
	}

	public function list()
	{
		if (!$this->isSessionOpen()) {
			throw new \Exception('The session must be started');
		}

		if (!empty($_SESSION[$this->appId][$this->domain])) {
			return $_SESSION[$this->appId][$this->domain];
		}

		return null;
	}

	public function clear()
	{
		if (!$this->isSessionOpen()) {
			throw new \Exception('The session must be started');
		}

		if (!empty($_SESSION[$this->appId][$this->domain])) {
			unset($_SESSION[$this->appId][$this->domain]);
		}
	}

	private function isSessionOpen() : bool
	{
		return (session_status() === PHP_SESSION_ACTIVE && session_id() !== '');
	}

}