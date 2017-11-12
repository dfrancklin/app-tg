<?php

namespace App\Interfaces\Repositories;

use App\Models\Product;

interface IProductRepository
{

	function all() : array;

	function page(int $page, int $quantity) : array;

	function byId(int $id);

	function save(Product $product);

	function delete(int $id) : bool;

	function total() : int;

}
