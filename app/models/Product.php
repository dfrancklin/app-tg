<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Product
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
	 * @ORM/Column(type=lob)
	 */
	public $description;

	/**
	 * @ORM/Column(type=lob)
	 */
	public $picture;

	/**
	 * @ORM/Column(type=float, scale=10, precision=2)
	 */
	public $price;

	/**
	 * @ORM/Column(type=int)
	 */
	public $quantity;

	/**
	 * @ORM/ManyToMany(class=App\Models\Category)
	 */
	public $categories;

	/**
	 * @ORM/HasMany(class=App\Models\ItemOrder)
	 */
	public $items;

}
