<?php

namespace App\Http\Models\JobEntities;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class JobEntityLang extends Model
{
	protected $table = 'job_entity_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'job_entity_id',
		'lang_id',
		'title',
		'address',
		'description',
		'short_description',
	];
}
