<?php

namespace App\Interfaces\Repositories;

interface IGenericRepository
{

	function all() : Array;

	function page(int $page, int $quantity) : Array;

	function byId(int $id);

	function save($object);

	function delete(int $id) : bool;

	function total() : int;

}
