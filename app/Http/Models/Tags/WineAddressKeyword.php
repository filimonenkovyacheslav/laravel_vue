<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class WineAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'wine_address_keywords';
	public static $tableName = 'wine_address_keywords';
	public static $relativeId = 'wine_id';
	public static $relativeTable = 'wines';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'wine_id',
		'key_id',
	];

}
