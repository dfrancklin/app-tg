<?php

namespace App\Services;

use App\Models\Product;
use App\Interfaces\Services\IProductService;
use App\Interfaces\Repositories\IProductRepository;

/**
 * @Service
 */
class ProductService implements IProductService
{

	private $repository;

	public function __construct(IProductRepository $repository)
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

	public function byId(int $id)
	{
		return $this->repository->byId($id);
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
