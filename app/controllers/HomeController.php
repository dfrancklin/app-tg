<?php

namespace App\Controllers;

use \FW\View\IViewFactory;

/**
 * @Controller
 * @Route /
 * @Authenticate
 */
class HomeController
{

	private $factory;

	public function __construct(IViewFactory $factory)
	{
		$this->factory = $factory;
	}

	public function index()
	{
		return $this->dashboard();
	}

	/**
	 * @RequestMap dashboard
	 */
	public function dashboard()
	{
		$view = $this->factory::create();

		$view->pageTitle = 'Dashboard';

		return $view->render('home/dashboard');
	}

}
