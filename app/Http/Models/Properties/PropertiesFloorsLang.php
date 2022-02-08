<?php

namespace App\Http\Models\Properties;

use Illuminate\Database\Eloquent\Model;

class PropertiesFloorsLang extends Model
{
	public $fillable = [
		'floor_id', 'lang_id', 'title', 'description',
	];
}
