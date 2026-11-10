<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class DesignAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'design_address_keywords';
	public static $tableName = 'design_address_keywords';
	public static $relativeId = 'design_id';
	public static $relativeTable = 'designs';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'design_id',
		'key_id',
	];

}
