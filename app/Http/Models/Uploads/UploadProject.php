<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;

class UploadProject extends Model
{
    public $timestamps = false;

	protected $table = 'uploads_projects';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
    protected $fillable = [
		'project_id',
		'upload_id',
	];
}
