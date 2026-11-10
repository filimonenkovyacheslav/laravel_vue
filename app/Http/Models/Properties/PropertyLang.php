<?php

namespace App\Http\Models\Properties;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class PropertyLang extends Model
{
	protected $table = 'property_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'property_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
