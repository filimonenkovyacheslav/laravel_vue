<?php

namespace App\Http\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class ProductLang extends Model
{
	protected $table = 'product_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'product_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
