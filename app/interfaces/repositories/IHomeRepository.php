<?php

namespace App\Interfaces\Repositories;

interface IHomeRepository
{

	function bestSellers() : Array;

	function salesByMonth(int $quantity) : Array;

	function lastSales() : Array;

	function bestCustomers() : Array;

	function customerLastBuy() : Array;

}
