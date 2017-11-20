<?php

namespace App\Repositories;

use App\Models\Customer;

use App\Interfaces\Repositories\ICustomersRepository;

/**
 * @Repository
 */
class CustomersRepository implements ICustomersRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function all() : Array
	{
		return $this->em->list(Customer::class);
	}

	public function page(int $page, int $quantity) : Array
	{
		return $this->em->list(Customer::class, $page, $quantity);
	}

	public function findById(int $id)
	{
		return $this->em->find(Customer::class, $id);
	}

	public function save($customer)
	{
		$this->em->beginTransaction();
		$customer = $this->em->save($customer);
		$this->em->commit();

		return $customer;
	}

	public function delete(int $id) : bool
	{
		$customer = $this->findById($id);

		$this->em->beginTransaction();
		$customer = $this->em->remove($customer);
		$this->em->commit();

		return $customer > 0;
	}

	public function total() : int
	{
		$query = $this->em->createQuery(Customer::class);
		$count = $query->count('c.id', 'total')->one();

		return $count['total'] ?? 0;
	}

}
