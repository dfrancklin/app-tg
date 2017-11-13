<?php

namespace App\Controllers;

use App\Interfaces\Services\IRolesService;

/**
 * @Controller
 * @Route /roles
 * @Authenticate
 */
class RolesController
{

	private $service;

	public function __construct(IRolesService $service)
	{
		$this->service = $service;
	}

	/**
	 * @RequestMap /json
	 * @RequestMethod POST
	 */
	public function rolesJson() {
		header('Content-type:application/json; charset=UTF-8');

		$list = $this->service->findByName($_POST['search']);

		return json_encode($list);
	}

}
