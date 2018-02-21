<?php

namespace App\Interfaces\Services;

interface IHomeService
{

	function bestSellers() : Array;

	function salesByMonth() : Array;

	function lastSales() : Array;

	function bestCustomers() : Array;

	function customerLastBuy() : Array;

}
