<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class GoodAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'good_address_keywords';
	public static $tableName = 'good_address_keywords';
	public static $relativeId = 'good_id';
	public static $relativeTable = 'goods';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'good_id',
		'key_id',
	];

}
