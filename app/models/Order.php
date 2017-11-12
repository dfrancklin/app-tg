<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=orders)
 */
class Order
{

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/BelongsTo(class=App\Models\Customer)
	 */
	public $customer;

	/**
	 * @ORM/BelongsTo(class=App\Models\Employee)
	 * @ORM/JoinColumn(name=salesman_id)
	 */
	public $salesman;

	/**
	 * @ORM/Column(type=datetime)
	 */
	public $date;

}
