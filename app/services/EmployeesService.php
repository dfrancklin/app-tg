<?php

namespace App\Services;

use App\Interfaces\Services\IEmployeesService;
use App\Interfaces\Repositories\IEmployeesRepository;

/**
 * @Service
 */
class EmployeesService implements IEmployeesService
{

	private $repository;

	public function __construct(IEmployeesRepository $repository)
	{
		$this->repository = $repository;
	}

	public function authenticate(String $email, String $pass)
	{
		$employee = $this->repository->authenticate($email, $pass);

		if (empty($employee)) {
			return null;
		}

		// force to lazy load the employee's roles
		$employee->roles;

		if (!empty($employee->roles)) {
			$employee->roles = array_map(function($role) {
				return $role->name;
			}, $employee->roles);
		}

		return $employee;
	}

	public function except($employee) : Array
	{
		return $this->repository->except($employee);
	}

	public function all() : Array
	{
		return $this->repository->all();
	}

	public function page(int $page, int $quantity) : Array
	{
		return $this->repository->page($page, $quantity);
	}

	public function findById(int $id)
	{
		return $this->repository->findById($id);
	}

	public function save($object)
	{
		return $this->repository->save($object);
	}

	public function delete(int $id) : bool
	{
		return $this->repository->delete($id);
	}

	public function totalPages(int $quantity) : int
	{
		$total = $this->repository->total();

		return ceil($total / $quantity);
	}

}
