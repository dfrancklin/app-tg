<?php

namespace App\Services;

use App\Interfaces\Services\IRolesService;
use App\Interfaces\Repositories\IRolesRepository;

/**
 * @Service
 */
class RolesService implements IRolesService
{

	private $repository;

	public function __construct(IRolesRepository $repository)
	{
		$this->repository = $repository;
	}

	public function searchByName(String $search) : Array
	{
		return $this->repository->searchByName($search);
	}

}
