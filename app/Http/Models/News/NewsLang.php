<?php

namespace App\Http\Models\News;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class NewsLang extends Model
{
	protected $table = 'news_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'news_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}
