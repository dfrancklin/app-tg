<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Customer
{

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/Column(length=50)
	 */
	public $name;

	/**
	 * @ORM/Column(length=50)
	 */
	public $email;

	/**
	 * @ORM/Column(length=20)
	 */
	public $phone;

	/**
	 * @ORM/HasMany(class=App\Models\Order)
	 */
	public $orders;

}
