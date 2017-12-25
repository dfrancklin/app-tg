<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=item_order)
 */
class ItemOrder
{

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/BelongsTo(class=App\Models\Order)
	 */
	public $order;

	/**
	 * @ORM/BelongsTo(class=App\Models\Product)
	 */
	public $product;

	/**
	 * @ORM/Column(type=int)
	 */
	public $quantity;

	/**
	 * @ORM/Column(type=float, scale=10, precision=2)
	 */
	public $price;

}
