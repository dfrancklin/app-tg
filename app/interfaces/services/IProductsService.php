<?php

namespace App\Interfaces\Services;

interface IProductsService extends IGenericService
{

	function searchByName(String $search) : Array;

}
