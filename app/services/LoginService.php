<?php

namespace App\Services;

use App\Interfaces\Services\ILoginService;
use App\Interfaces\Repositories\IUserRepository;

/**
 * @Service
 */
class LoginService implements ILoginService
{

	private $repository;

	public function __construct(IUserRepository $repository)
	{
		$this->repository = $repository;
	}

	public function authenticate($email, $pass)
	{
		return $this->repository->findOne($email, $pass);
	}

}
