<?php

namespace App\Interfaces\Services;

interface ICategoriesService extends IGenericService
{

	function searchByName(String $search) : Array;

}
