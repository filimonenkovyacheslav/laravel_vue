<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UploadFranchise extends Model
{
	public $timestamps = false;

	protected $table = 'uploads_franchises';
	
    protected $fillable = [
		'franchise_id',
		'upload_id',
	];

}
