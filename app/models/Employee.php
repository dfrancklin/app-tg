<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Employee
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
	 * @ORM/Column(length=100)
	 */
	public $password;

	/**
	 * @ORM/Column(name=admission_date, type=date)
	 */
	public $admissionDate;

	/**
	 * @ORM/ManyToMany(class=App\Models\Role)
	 * @ORM/JoinTable(tableName=employee_role, join={name=employee_id}, inverse={name=role_id})
	 */
	public $roles;

	/**
	 * @ORM/BelongsTo(class=App\Models\Employee, optional=true)
	 * @ORM/JoinColumn(name=supervisor_id)
	 */
	public $supervisor;

	/**
	 * @ORM/HasMany(class=App\Models\Order)
	 */
	public $orders;

}
