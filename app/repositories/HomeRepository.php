<?php

namespace App\Repositories;

use ORM\Constants\OrderTypes;

use App\Models\BestCustomers;
use App\Models\Customer;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesByMonth;

use App\Interfaces\Repositories\IHomeRepository;

/**
 * @Repository
 */
class HomeRepository implements IHomeRepository
{

	private $em;

	public function __construct(\ORM\Orm $orm)
	{
		$this->em = $orm->createEntityManager();
	}

	public function bestSellers() : Array
	{
		$query = $this->em->createQuery();
		$query->sum('io.quantity', 'total');
		$query->from(ItemOrder::class, 'io');
		$query->join(Order::class, 'o');
		$query->join(Product::class, 'p');
		$query->where('o.date')->greaterOrEqualsThan(
			date(
				'Y-m-d',
				mktime(
					0, 0, 0,
					date('m') - 1, date('d'), date('Y')
				)
			)
		);
		$query->groupBy('p.id', 'p.name');
		$query->orderBy('total', OrderTypes::DESC);
		$query->top(10);

		return $query->list();
	}

	public function salesByMonth() : Array
	{
		return $this->em->createQuery(SalesByMonth::class)->top(12)->list();
	}

	public function lastSales() : Array
	{
		$query = $this->em->createQuery(Order::class, 'o');

		$query->orderBy('o.finished', OrderTypes::ASC);
		$query->orderBy('o.id', OrderTypes::DESC);
		$query->top(10);

		return $query->list();;
	}

	public function bestCustomers() : Array
	{
		return $this->em->createQuery(BestCustomers::class)->top(10)->list();
	}

	public function customersThatNeedsAttention() : Array
	{
		$query = $this->em->createQuery();

		$query->max('o.date', 'date');
		$query->from(Order::class, 'o');
		$query->join(Customer::class, 'c');
		$query->having()->max('o.date')->lessOrEqualsThan(
			date(
				'Y-m-d',
				mktime(
					0, 0, 0,
					date('m') - 3, date('d'), date('Y')
				)
			)
		);
		$query->groupBy('o.customer', 'c.name');
		$query->orderBy('o.date', OrderTypes::DESC);
		$query->top(10);

		return $query->list();
	}

}
