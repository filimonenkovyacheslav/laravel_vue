<?php

namespace App\Http\Controllers\Agents;

use Illuminate\Http\Request;
use App\Http\Controllers\Profile\UserController;

class ArchitectController extends UserController
{
	public $model;
	public $tableKey = 'user_id';
}
