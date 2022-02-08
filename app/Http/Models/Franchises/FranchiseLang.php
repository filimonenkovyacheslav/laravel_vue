<?php

namespace App\Http\Models\Franchises;

use Illuminate\Database\Eloquent\Model;

class FranchiseLang extends Model
{
	protected $table = 'franchise_langs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'franchise_id',
		'lang_id',
		'title',
		'address',
		'description',
	];
}