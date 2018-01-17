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

	public function save($order)
	{
		$this->em->beginTransaction();

		if ($order->finished) {
			$this->updateProducts($order);
		}

		$order = $this->em->save($order);
		$this->em->commit();

		return $order;
	}

	public function delete(int $id) : bool
	{
		$order = $this->findById($id);

		$this->em->beginTransaction();
		$order = $this->em->remove($order);
		$this->em->commit();

		return $order > 0;
	}

	public function total() : int
	{
		$query = $this->em->createQuery(Order::class);
		$count = $query->count('o.id', 'total')->one();

		return $count['total'] ?? 0;
	}

	private function updateProducts($order)
	{
		if (empty($order->items)) {
			return;
		}

		foreach ($order->items as $item) {
			$product = $item->product;
			$newQuantity = $item->product->quantity - $item->quantity;
			$product->quantity = $newQuantity;

			$this->em->save($product);
		}
	}

}
