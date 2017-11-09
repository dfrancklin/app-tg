<?php

namespace FW\Security;

use FW\View\IViewFactory;

interface IAuthentication {

// 	public function __construct(ISecurityService $service, IViewFactory $factory);

	public function login($returnsTo);

	public function authenticate();

	public function forbidden($route);

	public function logout();

}
