<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use PHC\Components\Form;

use App\Models\Order;

use App\Interfaces\Services\IOrdersService;
use App\Interfaces\Services\ICustomersService;
use App\Interfaces\Services\IEmployeesService;

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
		IEmployeesService $employeesService
	)
	{
		$this->factory = $factory;
		$this->ordersService = $ordersService;
		$this->customersService = $customersService;
		$this->employeesService = $employeesService;
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
		} else {
			$this->message->error('A problem occurred while saving the order!');
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
		$view->employees = $this->createEmployeesList();
		$view->form = new Form;

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

	private function createEmployeesList() : Array
	{
		$employees = ['' => 'Employee'];

		array_map(function($item) use (&$employees) {
			return $employees[$item->id] = $item->name;
		}, $this->employeesService->all());

		return $employees;
	}

	private function createOrder() : Order
	{
		// $properties = ['id', 'name', 'email', 'admission-date', 'supervisor'];
		// $order = new Order;

		// foreach ($properties as $property) {
		// 	$value = $_POST[$property];

		// 	if (!empty($value)) {
		// 		if ($property === 'admission-date') {
		// 			$order->admissionDate = new \DateTime($value);
		// 		} else {
		// 			$order->{$property} = $value;
		// 		}
		// 	}
		// }

		// if (!empty($_POST['supervisor'])) {
		// 	$supervisor = new Order;
		// 	$supervisor->id = (int) $_POST['supervisor'];

		// 	$order->supervisor = $supervisor;
		// }

		// if (!empty($_POST['roles'])) {
		// 	$roles = [];

		// 	foreach ($_POST['roles'] as $r) {
		// 		$role = new Role;
		// 		$role->id = (int) $r['value'];
		// 		$role->name = $r['label'];
		// 		$roles[] = $role;
		// 	}

		// 	$order->roles = $roles;
		// }

		// return $order;
		die('Saving...');
	}

}
