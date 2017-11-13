<?php

namespace App\Interfaces\Repositories;

interface IRolesRepository
{

	function findByName(String $search) : Array;

}
