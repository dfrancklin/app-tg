<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use PHC\Components\Form;

use App\Models\Customer;

use App\Interfaces\Services\ICustomersService;

/**
 * @Controller
 * @Route /customers
 * @Authenticate
 */
class CustomersController
{

	private $factory;

	private $service;

	private $message;

	public function __construct(IViewFactory $factory, ICustomersService $service)
	{
		$this->factory = $factory;
		$this->service = $service;
		$this->message = FlashMessages::getInstance();
	}

	public function customers()
	{
		$quantity = 10;
		$page = $_GET['page'] ?? 1;
		$customers = $this->service->page($page, $quantity);
		$totalPages = $this->service->totalPages($quantity);

		if (!empty($totalPages) && $page > $totalPages) {
			Router::redirect('/customers');
		}

		$view = $this->factory::create();
		$view->pageTitle = 'Customers';
		$view->customers = $customers;
		$view->page = (int) $page;
		$view->totalPages = $totalPages;

		return $view->render('customers/home');
	}

	/**
	 * @RequestMap /form/{id}
	 */
	public function edit(int $id)
	{
		$customer = $this->service->findById($id);

		if ($customer) {
			return $this->form($customer);
		} else {
			$this->message->error('No customer with the ID ' . $id . ' was found!');
			Router::redirect('/customers');
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
		$customer = $this->createCustomer();
		$customer = $this->service->save($customer);

		if ($customer) {
			$this->message->info('Customer saved!');
		} else {
			$this->message->error('A problem occurred while saving the customer!');
		}

		Router::redirect('/customers');
	}

	/**
	 * @RequestMap /delete/{id}
	 */
	public function delete($id)
	{
		if ($this->service->delete($id)) {
			$this->message->info('Customer deleted!');
		} else {
			$this->message->error('A problem occurred while deleting the customer!');
		}

		Router::redirect('/customers');
	}

	private function form($customer = null)
	{
		$view = $this->factory::create();

		$view->pageTitle = (is_null($customer) ? 'New' : 'Update') . ' Customer';
		$view->customer = $customer;
		$view->form = new Form;

		return $view->render('customers/form');
	}

	private function createCustomer() : Customer
	{
		$properties = ['id', 'name', 'email', 'phone'];
		$customer = new Customer;

		foreach ($properties as $property) {
			if (!empty($_POST[$property])) {
				$customer->{$property} = $_POST[$property];
			}
		}

		return $customer;
	}

}
