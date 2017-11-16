<?php

namespace App\Interfaces\Repositories;

interface ICategoriesRepository extends IGenericRepository
{

	function searchByName(String $search) : Array;

}
