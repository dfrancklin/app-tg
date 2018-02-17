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

	public function __construct(IViewFactory $factory, \ORM\Orm $orm)
	{
		$this->factory = $factory;
		$this->orm = $orm;
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

		$em = $this->orm->createEntityManager();
		$query = $em->createQuery();
		$query->count('p.id', 'total');
		$query->select('p.name');
		$query->from(\App\Models\ItemOrder::class, 'io');
		$query->join(\App\Models\Order::class, 'o');
		$query->join(\App\Models\Product::class, 'p');
		$query->where('o.date')->greaterOrEqualsThan(
			date(
				'Y-m-d',
				mktime(
					0, 0, 0,
					date('m') - 1, date('d'), date('Y')
				)
			)
		);
		$query->groupBy('p.id');
		$query->orderBy('total', \ORM\Constants\OrderTypes::DESC);
		$query->top(10);

		$view->list = $query->list();

		$view->months = $em->createQuery(\App\Models\SalesByMonth::class)->list();

		return $view->render('home/dashboard');
	}

}
