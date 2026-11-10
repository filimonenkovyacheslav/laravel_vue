<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class UserAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'user_address_keywords';
	public static $tableName = 'user_address_keywords';
	public static $relativeId = 'user_id';
	public static $relativeTable = 'users';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'key_id',
	];

}
