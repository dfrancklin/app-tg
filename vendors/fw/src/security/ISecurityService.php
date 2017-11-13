<?php

namespace FW\Security;

interface ISecurityService
{

	function isAuthenticated() : bool;

	function getUserProfile();

	function hasRoles(Array $roles);

	function authenticate(UserProfile $userProfile, bool $remember) : bool;

	function logout();

}
