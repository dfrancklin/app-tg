<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use FW\Security\ISecurityService;

use PHC\Components\Form;

use App\Models\Employee;

use App\Interfaces\Services\IEmployeesService;

/**
 * @Controller
 * @Route /profile
 * @Authenticate
 */
class ProfileController
{

	private $factory;

	private $controller;

	private $security;

	private $message;

	public function __construct(
		IViewFactory $factory,
		ISecurityService $security,
		IEmployeesService $service,
		EmployeesController $controller
	)
	{
		$this->factory = $factory;
		$this->security = $security;
		$this->service = $service;
		$this->controller = $controller;
		$this->message = FlashMessages::getInstance();
	}

	public function profile()
	{
		$profile = $this->security->getUserProfile();
		$employee = $this->service->findByEmail($profile->getId());

		$view = $this->factory::create();

		$view->pageTitle = 'Profile';
		$view->employee = $employee;
		$view->form = new Form;

		return $view->render('profile/form');
	}

	/**
	 * @RequestMap /save
	 * @RequestMethod POST
	 */
	public function save()
	{
		$employee = $this->createEmployee();
		$old = $this->service->findById($employee->id);

		if (empty($_POST['password'])) {
			$this->message->warning('You must inform the password to continue!');

			return $this->profile();
		}

		if ($employee->password !== $old->password) {
			$this->message->warning('The password informed does not match with the current password!');

			return $this->profile();
		}

		if (!empty($_POST['new-password'])) {
			if (empty($_POST['confirm-password'])) {
				$this->message->warning('You must confirm the password!');

				return $this->profile();
			}

			if ($_POST['new-password'] != $_POST['confirm-password']) {
				$this->message->warning('The new password and the confirm password does not match!');

				return $this->profile();
			}

			$employee->password = md5($_POST['new-password']);
		}

		$employee = $this->service->save($employee);

		if ($employee) {
			$this->message->info('Employee saved!');
		} else {
			$this->message->error('A problem occurred while saving the employee!');
		}

		Router::redirect('/profile');
	}

	private function createEmployee() : Employee
	{
		$properties = ['id', 'name', 'email', 'password', 'admission-date'];
		$employee = new Employee;

		foreach ($properties as $property) {
			$value = $_POST[$property];

			if (!empty($value)) {
				if ($property === 'admission-date') {
					$employee->admissionDate = new \DateTime($value);
				} else if ($property === 'password') {
					$employee->{$password} = md5($value);
				} else {
					$employee->{$property} = $value;
				}
			}
		}

		return $employee;
	}

}
