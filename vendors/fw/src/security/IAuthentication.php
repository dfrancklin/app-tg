<?php

namespace FW\Security;

use FW\View\IViewFactory;

interface IAuthentication
{

	function login($returnsTo);

	function authenticate();

	function forbidden();

	function logout();

}
