<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=v_sales_by_month, mutable=false)
 */
class SalesByMonth
{

	public $total;

	public $month;

}
