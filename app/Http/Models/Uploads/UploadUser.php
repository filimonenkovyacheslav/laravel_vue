<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UploadUser extends Model
{
	public $timestamps = false;

	protected $table = 'uploads_users';
	
    protected $fillable = [
		'user_id',
		'upload_id',
	];

}
