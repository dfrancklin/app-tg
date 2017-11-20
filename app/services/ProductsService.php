<?php

namespace App\Services;

use App\Models\Product;
use App\Interfaces\Services\IProductsService;
use App\Interfaces\Repositories\IProductsRepository;

/**
 * @Service
 */
class ProductsService implements IProductsService
{

	private $repository;

	public function __construct(IProductsRepository $repository)
	{
		$this->repository = $repository;
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

	public function save($product)
	{
		return $this->repository->save($product);
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
