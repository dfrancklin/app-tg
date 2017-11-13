<?php

namespace FW\Security;

use \FW\Core\Config;

/**
 * @Service
 */
class SecurityService implements ISecurityService
{

	private $appId;

	private $config;

	public function __construct()
	{
		$this->config = Config::getInstance();
		$this->appId = $this->config->get('app-id');
	}

	public function isAuthenticated() : bool
	{
		if (!$this->isSessionOpen()) {
			return false;
		}

		// If there is no user is on session...
		if (is_null($this->getUserProfile())) {
			// ...look for cookies
			if (!$this->validateCookies()) {
				// If there is no user is on session
				// and no cookies was found,
				// there is no user logged in
				return false;
			}
		}

		return true;
	}

	private function validateCookies()
	{
		if (!array_key_exists($this->appId, $_COOKIE) || !array_key_exists('token', $_COOKIE[$this->appId])) {
			return;
		}

		$context = $this->config->get('context');
		$secretKey = $this->config->get('secret-key');
		$expiration = $this->config->get('valid-time-token');

		$prefix = sha1($secretKey);
		$secretKeyEnconded = base64_encode($secretKey);
		$token = $_COOKIE[$this->appId]['token'];

		// Remove prefix
		$token = substr($token, strlen($prefix));
		// Base64 decode
		$token = base64_decode($token);
		// Remove secret
		$payload = substr($token, strlen($secretKeyEnconded));
		// Base64 decode
		$payload = base64_decode($payload);
		// Remove 'payload='
		$userProfile = substr($payload, strlen('payload='));
		// Unserialize profile
		$userProfile = unserialize($userProfile);

		if ($userProfile && $userProfile instanceof UserProfile) {
			return $this->authenticate($userProfile, true);
		}

		return false;
	}

	public function getUserProfile()
	{
		if (!array_key_exists($this->appId, $_SESSION) || !array_key_exists('user-profile', $_SESSION[$this->appId])) {
			return null;
		}

		$userProfile = unserialize($_SESSION[$this->appId]['user-profile']);

		return $userProfile;
	}

	public function hasRoles(Array $roles)
	{
		if (!$this->isAuthenticated() || empty($roles)) {
			return false;
		}

		$userProfile = $this->getUserProfile();

		foreach ($roles as $role) {
			if (!in_array($role, $userProfile->getRoles())) {
				return false;
			}
		}

		return true;
	}

	public function hasAnyRoles(Array $roles)
	{
		if (!$this->isAuthenticated() || empty($roles)) {
			return false;
		}

		$userProfile = $this->getUserProfile();

		foreach ($roles as $role) {
			if (in_array($role, $userProfile->getRoles())) {
				return true;
			}
		}

		return false;
	}

	public function authenticate(UserProfile $userProfile, bool $remember) : bool
	{
		if (!$this->isSessionOpen()) {
			return false;
		}

		$userProfile = serialize($userProfile);
		$_SESSION[$this->appId]['user-profile'] = $userProfile;

		if ($remember) {
			$this->setCookie($userProfile);
		}

		return true;
	}

	private function setCookie($userProfile)
	{
		$context = $this->config->get('context');
		$secretKey = $this->config->get('secret-key');
		$expiration = $this->config->get('valid-time-token');

		$payload = base64_encode('payload=' . $userProfile);
		$secretKeyEnconded = base64_encode($secretKey);
		$prefix = sha1($secretKey);

		$token = $prefix . base64_encode($secretKeyEnconded . $payload);

		setcookie($this->appId . '[token]', $token, time() + $expiration, $context);
	}

	public function logout()
	{
		if ($this->isAuthenticated()) {
			unset($_SESSION[$this->appId]['user-profile']);
		}

		if (!count($_SESSION[$this->appId])) {
			unset($_SESSION[$this->appId]);
		}

		if (!count($_SESSION)) {
			session_destroy();
		}

		$this->unsetCookie();
	}

	private function unsetCookie()
	{
		if (!array_key_exists($this->appId, $_COOKIE) || !array_key_exists('token', $_COOKIE[$this->appId])) {
			return;
		}

		$context = $this->config->get('context');

		setcookie($this->appId . '[token]', null, time() - 1000, $context);
	}

	private function isSessionOpen() : bool
	{
		return (session_status() === PHP_SESSION_ACTIVE && session_id() !== '');
	}

}
