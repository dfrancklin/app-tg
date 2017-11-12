<?php

namespace App\Helpers;

use App\Models\Role;

use ORM\Interfaces\IEntityManager;

class InitDatabase
{

	private $roles;

	public function beforeDrop(IEntityManager $em)
	{
		$this->roles = $em->list(Role::class);
	}

	public function afterCreate(IEntityManager $em)
	{
		if (empty($this->roles)) {
			$this->roles = $this->loadPreDefined('roles');
		}

		try {
			$em->beginTransaction();
			$em->save($this->roles);
			$em->commit();
		} catch (\Exception $e) {
			vd($e);
		}
	}

	private function loadPreDefined($data)
	{
		$preDefined = [];

		$roles = ['ADMIN', 'SALESMAN', 'STOCK'];

		foreach ($roles as $name) {
			$role = new Role();
			$role->name = $name;
			$preDefined['roles'][] = $role;
		}

		return $preDefined[$data];
	}

}
