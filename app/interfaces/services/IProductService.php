<?php

namespace App\Interfaces\Services;

use App\Models\Product;

interface IProductService
{

	function all() : array;

	function page(int $page, int $quantity) : array;

	function byId(int $id);

	function save(Product $product);

	function delete(int $id) : bool;

	function totalPages(int $quantity) : int;

}
