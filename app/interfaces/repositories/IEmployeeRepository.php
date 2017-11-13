<?php

namespace App\Interfaces\Repositories;

interface IEmployeeRepository extends IGenericRepository
{

	function authenticate(String $email, String $password);

}
