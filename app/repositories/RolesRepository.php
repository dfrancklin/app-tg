<?php

namespace App\Repositories;

use App\Interfaces\Repositories\IRolesRepository;

/**
 * @Repository
 */
class RolesRepository implements IRolesRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function searchByName(String $search) : Array
	{
		$query = $this->em->createQuery(\App\Models\Role::class);

		if (!empty($search)) {
			$query->where('r.name')->contains($search);
		}

		$list = $query->list();

		if (!empty($list)) {
			return $list;
		}

		return [];
	}

}
