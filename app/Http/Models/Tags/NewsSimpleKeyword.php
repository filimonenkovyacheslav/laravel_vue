<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class NewsSimpleKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'news_simple_keywords';
	public static $tableName = 'news_simple_keywords';
	public static $relativeId = 'news_id';
	public static $relativeTable = 'news';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'news_id',
		'key_id',
	];

}
