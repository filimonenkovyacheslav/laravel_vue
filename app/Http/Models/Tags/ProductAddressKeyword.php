<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;

class ProductAddressKeyword extends Model
{
	public $timestamps = false;

	protected $table = 'product_address_keywords';
	public static $tableName = 'product_address_keywords';
	public static $relativeId = 'product_id';
	public static $relativeTable = 'products';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'product_id',
		'key_id',
	];

}
