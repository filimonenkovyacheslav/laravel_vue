<?php

namespace App\Http\Models\Arts;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class ArtLang extends Model
{
	protected $table = 'art_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'art_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
