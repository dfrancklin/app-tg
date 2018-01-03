<?php

namespace App\Services;

use App\Interfaces\Services\IOrdersService;
use App\Interfaces\Repositories\IOrdersRepository;

/**
 * @Service
 */
class OrdersService implements IOrdersService
{

	private $repository;

	public function __construct(IOrdersRepository $repository)
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
