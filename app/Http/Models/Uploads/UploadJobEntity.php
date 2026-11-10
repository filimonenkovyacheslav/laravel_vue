<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UploadJobEntity extends Model
{
	public $timestamps = false;

	protected $table = 'uploads_job_entities';

    protected $fillable = [
		'job_entity_id',
		'upload_id',
	];


}
