<?php

namespace App\Interfaces\Services;

interface IEmployeeService extends IGenericService
{

	function authenticate(String $email, String $password);

}
