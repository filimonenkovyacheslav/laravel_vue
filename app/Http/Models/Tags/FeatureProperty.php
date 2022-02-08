<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class FeatureProperty extends Model
{
	public $timestamps = false;

	protected $table = 'features_properties';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'property_id',
		'feature_id',
	];
}
