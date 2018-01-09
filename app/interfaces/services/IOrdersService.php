<?php

namespace App\Interfaces\Services;

interface IOrdersService extends IGenericService
{

	function finish($id);

}
