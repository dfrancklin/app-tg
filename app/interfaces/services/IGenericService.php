<?php

namespace App\Interfaces\Services;

interface IGenericService
{

	function all() : Array;

	function page(int $page, int $quantity) : Array;

	function findById(int $id);

	function save($object);

	function delete(int $id) : bool;

	function totalPages(int $quantity) : int;

}
