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
		$quantity = 12;
		$sales = $this->repository->salesByMonth($quantity);
		$months = [];
		$currentMonth = date('m');
		$currentYear = date('Y');

		while($quantity--) {
			$month = sprintf('%04d-%02d', $currentYear, $currentMonth);
			$total = 0;

			if (($i = array_search($month, array_column($sales, 'month'))) !== false) {
				$total = $sales[$i]->total;
			}

			$months[] = (object) [
				'month' => $month,
				'total' => $total
			];

			$currentMonth--;

			if ($currentMonth === 0) {
				$currentYear--;
				$currentMonth = 12;
			}
		}

		return $months;
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
