<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;
use FW\View\IViewFactory;

use PHC\Components\FormComponent;

use App\Models\Employee;
use App\Interfaces\Services\IEmployeesService;

/**
 * @Controller
 * @Route /employees
 * @Authenticate
 */
class EmployeesController
{

	private $factory;

	private $service;

	private $message;

	public function __construct(IViewFactory $factory, IEmployeesService $service)
	{
		$this->factory = $factory;
		$this->service = $service;
		$this->message = FlashMessages::getInstance();
	}

	public function employees()
	{
		$quantity = 10;
		$page = $_GET['page'] ?? 1;
		$employees = $this->service->page($page, $quantity);
		$totalPages = $this->service->totalPages($quantity);

		if (!empty($totalPages) && $page > $totalPages) {
			Router::redirect('/employees');
		}

		$view = $this->factory::create();
		$view->pageTitle = 'Employees';
		$view->employees = $employees;
		$view->page = (int) $page;
		$view->totalPages = $totalPages;

		return $view->render('employees/home');
	}

	/**
	 * @RequestMap /form/{id}
	 */
	public function edit(int $id)
	{
		$employee = $this->service->findById($id);

		if ($employee) {
			return $this->form($employee);
		} else {
			$this->message->error('No product with the ID ' . $id . ' was found!');
			Router::redirect('/employees');
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
		$employee = $this->createEmployee();
		$employee = $this->service->save($employee);

		if ($employee) {
			$this->message->info('Employee saved!');
		} else {
			$this->message->error('A problem occurred while saving the product!');
		}

		Router::redirect('/employees');
	}

	/**
	 * @RequestMap /delete/{id}
	 * @RequestMethod POST
	 */
	public function delete($id)
	{
		if ($this->service->delete($id)) {
			$this->message->info('Employee deleted!');
		} else {
			$this->message->error('A problem occurred while deleting the product!');
		}

		Router::redirect('/employees');
	}

	private function form($employee = null)
	{
		$view = $this->factory::create();

		$view->pageTitle = (is_null($employee) ? 'New' : 'Update') . ' Employee';
		$view->product = $employee;
		$view->form = new FormComponent;

		return $view->render('employees/form');
	}

	private function createEmployee() : Employee
	{
		die();
		$properties = ['id', 'name', 'description', 'price', 'quantity'];
		$employee = new Employee;

		foreach ($properties as $property) {
			$value = $_POST[$property];

			if (!empty($value)) {
				if (is_numeric($value)) {
					if (is_int($value)) {
						$employee->{$property} = (int) $value;
					} elseif (is_float($value)) {
						$employee->{$property} = (float) $value;
					} else {
						$employee->{$property} = $value + 0;
					}
				} else {
					$employee->{$property} = $value;
				}
			}
		}

		if (isset($_FILES['picture']) && !$_FILES['picture']['error']) {
			$mime = $_FILES['picture']['type'];
			$file = file_get_contents($_FILES['picture']['tmp_name']);
			$picture = sprintf('data:%s;base64,%s', $mime, base64_encode($file));
			$employee->picture = $picture;
		} else {
			if (!empty($employee->id)) {
				$old = $this->service->findById($employee->id);

				if (!empty($old)) {
					$employee->picture = $old->picture;
				}
			}
		}

		if (!empty($_POST['categories'])) {
			$categories = [];

			foreach ($_POST['categories'] as $id) {
				$category = new \App\Models\Category;
				$category->id = $id;
				$categories[] = $category;
			}

			$employee->categories = $categories;
		}

		return $employee;
	}

}
