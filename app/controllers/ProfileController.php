<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use FW\Security\ISecurityService;

use PHC\Components\Form;

use App\Interfaces\Services\IEmployeesService;

/**
 * @Controller
 * @Route /profile
 * @Authenticate
 */
class ProfileController
{

	private $factory;

	private $service;

	private $security;

	private $message;

	public function __construct(
		IViewFactory $factory,
		ISecurityService $security,
		IEmployeesService $service
	)
	{
		$this->factory = $factory;
		$this->service = $service;
		$this->security = $security;
		$this->message = FlashMessages::getInstance();
	}

	public function profile()
	{
		return 'Profile page!';
	}

}
