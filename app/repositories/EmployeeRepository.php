<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Interfaces\Repositories\IEmployeeRepository;

/**
 * @Repository
 */
class EmployeeRepository implements IEmployeeRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function authenticate(String $email, String $password)
	{
		$query = $this->em->createQuery(Employee::class);
		$query->where('e.email')->eq($email);
		$query->where('e.password')->eq(md5($password));
		$employee = $query->one();

		return $employee;
	}

	public function all() : Array
	{
		return $this->em->list(Employee::class);
	}

	public function page(int $page, int $quantity) : Array
	{
		return $this->em->list(Employee::class, $page, $quantity);
	}

	public function findById(int $id)
	{
		return $this->em->find(Employee::class, $id);
	}

	public function save($employee)
	{
		$this->em->beginTransaction();
		$employee = $this->em->save($employee);
		$this->em->commit();

		return $employee;
	}

	public function delete(int $id) : bool
	{
		$employee = $this->findById($id);

		$this->em->beginTransaction();
		$employee = $this->em->remove($employee);
		$this->em->commit();

		return $employee > 0;
	}

	public function total() : int
	{
		$query = $this->em->createQuery(Employee::class);
		$count = $query->count('p.id', 'total')->one();

		return $count['total'] ?? 0;
	}

}
