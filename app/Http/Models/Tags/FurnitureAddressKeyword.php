<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class FurnitureAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'furniture_address_keywords';
	public static $tableName = 'furniture_address_keywords';
	public static $relativeId = 'furniture_id';
	public static $relativeTable = 'furnitures';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'furniture_id',
		'key_id',
	];

}
