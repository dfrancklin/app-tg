<?php

namespace App\Services;

use App\Interfaces\Services\IEmployeeService;
use App\Interfaces\Repositories\IEmployeeRepository;

/**
 * @Service
 */
class EmployeeService implements IEmployeeService
{

	private $repository;

	public function __construct(IEmployeeRepository $repository)
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

	public function all() : Array
	{
		return $this->repository->all();
	}

	public function page(int $page, int $quantity) : Array
	{
		return $this->repository->page($page, $quantity);
	}

	public function byId(int $id)
	{
		return $this->repository->byId();
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
