<?php

namespace App\Repositories;

use App\Models\Category;

use App\Interfaces\Repositories\ICategoriesRepository;

/**
 * @Repository
 */
class CategoriesRepository implements ICategoriesRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function all() : Array
	{
		return $this->em->list(Category::class);
	}

	public function page(int $page, int $quantity) : Array
	{
		return $this->em->list(Category::class, $page, $quantity);
	}

	public function findById(int $id)
	{
		return $this->em->find(Category::class, $id);
	}

	public function searchByName(String $search) : Array
	{
		$query = $this->em->createQuery(Category::class);

		if (!empty($search)) {
			$query->where('c.name')->contains($search);
		}

		$list = $query->list();

		if (!empty($list)) {
			return $list;
		}

		return [];
	}

	public function save($category)
	{
		$this->em->beginTransaction();
		$category = $this->em->save($category);
		$this->em->commit();

		return $category;
	}

	public function delete(int $id) : bool
	{
		$category = $this->findById($id);

		$this->em->beginTransaction();
		$category = $this->em->remove($category);
		$this->em->commit();

		return $category > 0;
	}

	public function total() : int
	{
		$query = $this->em->createQuery(Category::class);
		$count = $query->count('c.id', 'total')->one();

		return $count['total'] ?? 0;
	}

}
