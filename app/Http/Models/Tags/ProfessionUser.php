<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class ProfessionUser extends Model
{
	public $timestamps = false;

	protected $table = 'professions_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'profession_id',
	];
}
