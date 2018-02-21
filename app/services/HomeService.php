<?php

namespace App\Services;

use App\Interfaces\Services\IHomeService;
use App\Interfaces\Repositories\IHomeRepository;

/**
 * @Service
 */
class HomeService implements IHomeService
{

	private $repository;

	public function __construct(IHomeRepository $repository)
	{
		$this->repository = $repository;
	}

	public function bestSellers() : Array
	{
		return $this->repository->bestSellers();
	}

	public function salesByMonth() : Array
	{
		return $this->repository->salesByMonth();
	}

	public function lastSales() : Array
	{
		return $this->repository->lastSales();
	}

	public function bestCustomers() : Array
	{
		return $this->repository->bestCustomers();
	}

	public function customerLastBuy() : Array
	{
		return $this->repository->customerLastBuy();
	}

}
