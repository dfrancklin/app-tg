<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Role
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
	 * @ORM/ManyToMany(class=App\Models\Employee, mappedBy=roles)
	 */
	public $employees;

}
