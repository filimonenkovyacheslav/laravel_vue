<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UploadProfession extends Model
{
	public $timestamps = false;

	protected $table = 'uploads_professions';

    protected $fillable = [
		'profession_id',
		'upload_id',
	];

}
