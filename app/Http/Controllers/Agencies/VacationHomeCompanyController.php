<?php

namespace App\Http\Controllers\Agencies;

use Illuminate\Http\Request;
use App\Http\Controllers\Profile\UserController;

class VacationHomeCompanyController extends UserController
{
	public $model;
	public $tableKey = 'user_id';
}
