<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;
use FW\View\IViewFactory;
use FW\Security\ISecurityService;

use PHC\Components\FormComponent;

use App\Models\Role;
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

	private $security;

	private $message;

	public function __construct(
		IViewFactory $factory,
		IEmployeesService $service,
		ISecurityService $security
	)
	{
		$this->factory = $factory;
		$this->service = $service;
		$this->security = $security;
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
			$this->message->error('No employee with the ID ' . $id . ' was found!');
			$this->message->error('No supervisors with the ID ' . $id . ' was found!');
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

		if (!empty($employee->id)) {
			$old = $this->service->findById($employee->id);

			if(!$this->security->hasRoles(['ADMIN'])) {
				if (empty($_POST['password'])) {
					$this->message->warning('You must inform the password to continue!');

					return $this->form($employee);
				}

				if (empty($old)) {
					$this->message->error('Error while saving');
					Router::redirect('/employees');
					
					return;
				}

				if (md5($_POST['password']) !== $old->password) {
					$this->message->warning('The password informed does not match with the current password!');

					return $this->form($employee);
				}
			}

			$employee->password = $old->password;
		}

		if (empty($employee->id) && empty($_POST['new-password'])) {
			$this->message->warning('You must inform the password!');

			return $this->form($employee);
		}

		if (!empty($_POST['new-password'])) {
			if (empty($_POST['confirm-password'])) {
				$this->message->warning('You must confirm the password!');

				return $this->form($employee);
			}

			if ($_POST['new-password'] != $_POST['confirm-password']) {
				$this->message->warning('The new password and the confirm password does not match!');

				return $this->form($employee);
			}

			$employee->password = md5($_POST['new-password']);
		}

		$employee = $this->service->save($employee);

		if ($employee) {
			$this->message->info('Employee saved!');
		} else {
			$this->message->error('A problem occurred while saving the employee!');
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
			$this->message->error('A problem occurred while deleting the employee!');
		}

		Router::redirect('/employees');
	}

	private function form($employee = null)
	{
		$view = $this->factory::create();

		if (!empty($employee)) {
			$employees = $this->service->except($employee);
		} else {
			$employees = $this->service->all();
		}

		$supervisors = ['' => 'Supervisor'];

		if (!empty($employees)) {
			foreach ($employees as $e) {
				$supervisors[$e->id] = $e->name;
			}
		}

		$view->pageTitle = (is_null($employee) ? 'New' : 'Update') . ' Employee';
		$view->employee = $employee;
		$view->supervisors = $supervisors;
		$view->form = new FormComponent;

		return $view->render('employees/form');
	}

	private function createEmployee() : Employee
	{
		$properties = ['id', 'name', 'email', 'admission-date', 'supervisor'];
		$employee = new Employee;

		foreach ($properties as $property) {
			$value = $_POST[$property];

			if (!empty($value)) {
				if ($property === 'admission-date') {
					$employee->admissionDate = new \DateTime($value);
				} else {
					$employee->{$property} = $value;
				}
			}
		}

		if (!empty($_POST['supervisor'])) {
			$supervisor = new Employee;
			$supervisor->id = (int) $_POST['supervisor'];

			$employee->supervisor = $supervisor;
		}

		if (!empty($_POST['roles'])) {
			$roles = [];

			foreach ($_POST['roles'] as $r) {
				$role = new Role;
				$role->id = (int) $r['value'];
				$role->name = $r['label'];
				$roles[] = $role;
			}

			$employee->roles = $roles;
		}

		return $employee;
	}

}
