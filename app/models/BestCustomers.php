<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=v_best_customers, mutable=false)
 */
class BestCustomers
{

	/**
	 * @ORM/Id
	 * @ORM/Column(name=customer_id, type=int)
	 */
	public $id;

	public $total;

	/**
	 * @ORM/BelongsTo(class=App\Models\Customer)
	 */
	public $customer;

}
