<?php

namespace FW\Security;

interface ISecurityService
{

	function isAuthenticated() : bool;

	function getUserProfile();

	function hasRoles(array $roles);

	function authenticate(UserProfile $userProfile, bool $remember) : bool;

	function logout();

}
