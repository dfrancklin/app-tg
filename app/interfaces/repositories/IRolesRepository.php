<?php

namespace App\Interfaces\Repositories;

interface IRolesRepository
{

	function searchByName(String $search) : Array;

}
