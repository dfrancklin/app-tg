<?php

namespace App\Controllers;

use FW\View\IViewFactory;

use App\Interfaces\Services\IHomeService;

/**
 * @Controller
 * @Route /
 * @Authenticate
 */
class HomeController
{

	private $factory;

	private $service;

	public function __construct(IViewFactory $factory, IHomeService $service)
	{
		$this->factory = $factory;
		$this->service = $service;
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
		$view->bestSellers = $this->service->bestSellers();
		$view->salesByMonth = $this->service->salesByMonth();
		$view->lastSales = $this->service->lastSales();
		$view->bestCustomers = $this->service->bestCustomers();
		$view->customerLastBuy = $this->service->customerLastBuy();

		return $view->render('home/dashboard');
	}

}
