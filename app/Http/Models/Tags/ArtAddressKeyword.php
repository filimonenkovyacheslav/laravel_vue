<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class ArtAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'art_address_keywords';
	public static $tableName = 'art_address_keywords';
	public static $relativeId = 'art_id';
	public static $relativeTable = 'arts';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'art_id',
		'key_id',
	];

}
