<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Category
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
	 * @ORM/ManyToMany(class=App\Models\Product, mappedBy=categories)
	 */
	public $products;

}
