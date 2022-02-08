<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class PropertyAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'property_address_keywords';
	public static $tableName = 'property_address_keywords';
	public static $relativeId = 'property_id';
	public static $relativeTable = 'properties';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'property_id',
		'key_id',
	];

}
