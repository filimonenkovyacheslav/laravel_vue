<?php

namespace App\Http\Models\Furnitures;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class FurnitureLang extends Model
{
	protected $table = 'furniture_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'furniture_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
