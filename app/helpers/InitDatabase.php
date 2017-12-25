<?php

namespace App\Helpers;

use App\Models\Role;
use App\Models\Employee;

use ORM\Interfaces\IEntityManager;

class InitDatabase
{

	public function beforeDrop(IEntityManager $em)
	{}

	public function afterCreate(IEntityManager $em)
	{
		$roles = $this->loadPreDefined('roles');
		$employee = $this->loadPreDefined('employees')[0];
		$employee->roles = $roles;

		$em->beginTransaction();
		$em->save($roles);
		$em->save($employee);
		$em->commit();
	}

	private function loadPreDefined($name)
	{
		$repo = [
			'roles' => [
				'class' => Role::class,
				'registers' => [
					['name' => 'ADMIN'],
					['name' => 'SALES'],
					['name' => 'STOCK'],
				]
			],
			'employees' => [
				'class' => Employee::class,
				'registers' => [
					[
						'name' => 'Default User',
						'email' => 'default@user.com',
						'password' => md5(123),
						'admissionDate' => new \DateTime,
					],
				]
			],
		];

		if (!array_key_exists($name, $repo)) {
			return;
		}

		$objects = [];
		$class = $repo[$name]['class'];
		$registers = $repo[$name]['registers'];

		foreach ($registers as $register) {
			$object = new $class();

			foreach ($register as $property => $value) {
				$object->{$property} = $value;
			}

			$objects[] = $object;
		}

		vd($objects);

		return $objects;
	}

}
