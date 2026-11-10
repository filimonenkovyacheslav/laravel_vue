<?php

namespace App\Http\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class GoodLang extends Model
{
	protected $table = 'good_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'good_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
