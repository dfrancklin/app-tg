<?php

namespace App\Repositories;

use App\Models\Order;

use App\Interfaces\Repositories\IOrdersRepository;

/**
 * @Repository
 */
class OrdersRepository implements IOrdersRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function all() : Array
	{
		return $this->em->list(Order::class);
	}

	public function page(int $page, int $quantity) : Array
	{
		return $this->em->list(Order::class, $page, $quantity);
	}

	public function findById(int $id)
	{
		return $this->em->find(Order::class, $id);
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
		$query = $this->em->createQuery(Order::class);
		$count = $query->count('o.id', 'total')->one();

		return $count['total'] ?? 0;
	}

}
