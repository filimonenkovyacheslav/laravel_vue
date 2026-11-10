<?php

namespace App\Http\Models\Wines;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class WineLang extends Model
{
	protected $table = 'wine_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'wine_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
