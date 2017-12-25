<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use PHC\Components\Form;

// use App\Interfaces\Services\IOrdersService;

/**
 * @Controller
 * @Route /orders
 * @Authenticate
 * @Roles [ADMIN, SALES]
 */
class OrdersController
{

	private $service;

	public function __construct(IViewFactory $factory/*, IOrdersService $service*/)
	{
		$this->factory = $factory;
		// $this->service = $service;
		$this->message = FlashMessages::getInstance();
	}

	public function home()
	{
		Router::redirect('/');
	}


}
