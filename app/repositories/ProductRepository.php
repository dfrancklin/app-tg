<?php

namespace App\Repositories;

use App\Models\Product;

use App\Interfaces\Repositories\IProductRepository;

/**
 * @Repository
 */
class ProductRepository implements IProductRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function all() : array
	{
		return $this->em->list(Product::class);
	}

	public function page(int $page, int $quantity) : array
	{
		return $this->em->list(Product::class, $page, $quantity);
	}

	public function byId(int $id)
	{
		return $this->em->find(Product::class, $id);
	}

	public function save(Product $product)
	{
		$this->em->beginTransaction();
		$product = $this->em->save($product);
		$this->em->commit();

		return $product;
	}

	public function delete(int $id) : bool
	{
		$product = $this->byId($id);

		$this->em->beginTransaction();
		$product = $this->em->remove($product);
		$this->em->commit();

		return $product > 0;
	}

	public function total() : int
	{
		$query = $this->em->createQuery(Product::class);
		$count = $query->count('p.id', 'total')->one();

		return $count['total'] ?? 0;
	}

}
