<?php

namespace App\Interfaces\Repositories;

interface IEmployeesRepository extends IGenericRepository
{

	function authenticate(String $email, String $password);

	function except($employee) : Array;

	function findByEmail(String $email);

}
