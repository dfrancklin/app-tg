<?php

namespace App\Interfaces\Services;

interface IRolesService
{

	function findByName(String $search) : Array;

}
