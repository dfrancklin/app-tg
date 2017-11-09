<?php

namespace FW\Security;

interface ISecurityService {

	public function isAuthenticated() : bool;

	public function getUserProfile();

	public function hasRoles(array $roles);

	public function authenticate(UserProfile $userProfile, bool $remember) : bool;

	public function logout();

}
