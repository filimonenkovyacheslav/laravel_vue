<?php

namespace App\Http\Models\Designs;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class DesignLang extends Model
{
	protected $table = 'design_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'design_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
