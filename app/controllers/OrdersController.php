<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use FW\Security\ISecurityService;

use PHC\Components\Form;

use App\Models\Order;

use App\Interfaces\Services\IOrdersService;
use App\Interfaces\Services\ICustomersService;
use App\Interfaces\Services\IEmployeesService;

use App\Components\ProductPicklist;

/**
 * @Controller
 * @Route /orders
 * @Authenticate
 * @Roles [ADMIN, SALES]
 */
class OrdersController
{

	private $ordersService;

	private $customersService;

	private $employeesService;

	private $message;

	public function __construct(
		IViewFactory $factory,
		IOrdersService $ordersService,
		ICustomersService $customersService,
		IEmployeesService $employeesService,
		ISecurityService $security
	)
	{
		$this->factory = $factory;
		$this->ordersService = $ordersService;
		$this->customersService = $customersService;
		$this->employeesService = $employeesService;
		$this->security = $security;
		$this->message = FlashMessages::getInstance();
	}

	public function orders()
	{
		$quantity = 10;
		$page = $_GET['page'] ?? 1;
		$orders = $this->ordersService->page($page, $quantity);
		$totalPages = $this->ordersService->totalPages($quantity);

		if (!empty($totalPages) && $page > $totalPages) {
			Router::redirect('/orders');
		}

		$view = $this->factory::create();
		$view->pageTitle = 'Orders';
		$view->orders = $orders;
		$view->page = (int) $page;
		$view->totalPages = $totalPages;

		return $view->render('orders/home');
	}

	/**
	 * @RequestMap /view/{id}
	 */
	public function view(int $id)
	{
		$order = $this->ordersService->findById($id);

		if (!$order) {
			$this->message->error('No order with the ID ' . $id . ' was found!');

			Router::redirect('/orders');
		}

		$view = $this->factory::create();
		$view->pageTitle = 'View Order';
		$view->order = $order;
		$view->canEdit = $this->security->hasAnyRoles(['ADMIN', 'SALES']);

		return $view->render('orders/view');
	}


	/**
	 * @RequestMap /form/{id}
	 */
	public function edit(int $id)
	{
		$order = $this->ordersService->findById($id);

		if ($order) {
			if ($order->finished) {
				Router::redirect('/orders/view/' . $id);
			}

			return $this->form($order);
		} else {
			$this->message->error('No order with the ID ' . $id . ' was found!');

			Router::redirect('/orders');
		}
	}

	/**
	 * @RequestMap /form
	 */
	public function create()
	{
		return $this->form();
	}

	/**
	 * @RequestMethod POST
	 */
	public function save()
	{
		$order = $this->createOrder();
		$order = $this->ordersService->save($order);

		if ($order) {
			$this->message->info('Order saved!');
			Router::redirect('/orders/form/' . $order->id);
		} else {
			$this->message->error('A problem occurred while saving the order!');
			Router::redirect('/orders');
		}
	}

	/**
	 * @RequestMap /finish/{id}
	 * @RequestMethod POST
	 */
	public function finish($id)
	{
		$order = $this->createOrder();

		if ($this->ordersService->finish($order)) {
			$this->message->info('Order finished!');
		} else {
			$this->message->error('A problem occurred while finishing the order!');
		}

		Router::redirect('/orders');
	}

	/**
	 * @RequestMap /delete/{id}
	 */
	public function delete($id)
	{
		if ($this->ordersService->delete($id)) {
			$this->message->info('Order deleted!');
		} else {
			$this->message->error('A problem occurred while deleting the order!');
		}

		Router::redirect('/orders');
	}

	private function form($order = null)
	{
		$view = $this->factory::create();

		$view->pageTitle = (is_null($order) ? 'New' : 'Update') . ' Order';
		$view->order = $order;
		$view->customers = $this->createCustomersList();
		$view->form = new Form;
		$view->scripts = ['/public/js/ProductPicklist.js'];

		Form::use('products', ProductPicklist::class);

		return $view->render('orders/form');
	}

	private function createCustomersList() : Array
	{
		$customers = ['' => 'Customer'];

		array_map(function($item) use (&$customers) {
			return $customers[$item->id] = $item->name;
		}, $this->customersService->all());

		return $customers;
	}

	private function createOrder() : Order
	{
		$order = new Order;

		if (!empty($_POST['id'])) {
			$order->id = (int) $_POST['id'];
		}

		$order->date = new \DateTime;
		$order->finished = false;

		$order->customer = new \App\Models\Customer;
		$order->customer->id = (int) $_POST['customer'];

		$email = $this->security->getUserProfile()->getId();
		$order->salesman = $this->employeesService->findByEmail($email);

		if (!empty($_POST['products'])) {
			$items = [];

			foreach ($_POST['products'] as $product) {
				$item = new \App\Models\ItemOrder;

				$item->order = $order;
				$item->product = new \App\Models\Product;
				$item->product->id = (int) $product['id'];
				$item->product->name = $product['name'];
				$item->product->picture = $product['picture'];
				$item->quantity = $product['quantity'];
				$item->price = $product['price'];

				$items[] = $item;
			}

			$order->items = $items;
		}

		return $order;
	}

}
