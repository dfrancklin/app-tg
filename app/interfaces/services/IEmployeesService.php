<?php

namespace App\Interfaces\Services;

interface IEmployeesService extends IGenericService
{

	function authenticate(String $email, String $password);

	function except($employee) : Array;

}
