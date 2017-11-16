<?php

namespace App\Services;

use App\Models\Category;
use App\Interfaces\Services\ICategoriesService;
use App\Interfaces\Repositories\ICategoriesRepository;

/**
 * @Service
 */
class CategoriesService implements ICategoriesService
{

	private $repository;

	public function __construct(ICategoriesRepository $repository)
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

	public function searchByName(String $search) : Array
	{
		return $this->repository->searchByName($search);
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
