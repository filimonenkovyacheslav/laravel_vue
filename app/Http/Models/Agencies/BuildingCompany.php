<?php

namespace App\Http\Models\Agencies;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class BuildingCompany extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return ['company_slug' => ['source' => 'company_name']];
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'lang_id',
		'company_name',
		'license',
		'opening_hours',
		'description',
	];

	public static $type = 'building_company';
	public static $tableName = 'building_companies';
	public static $key = 'user_id';

	public static $translatable = [
		'company_name', 'description', 'opening_hours',
	];

	public static $selectable = [
		'company_slug', 'company_name', 'license', 'description', 'opening_hours',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public static function _getFieldsList() {
		return [
			'relation' => [
				'name' => [
					'index' => 'company_name',
					'type' => 'text',
					'label' => __('Agency Name'),
					'value' => ['user', 'company_name'],
				],
				'license' => [
					'index' => 'license',
					'type' => 'text',
					'label' => __('Agency License Number'),
					'value' => ['user', 'license'],
				],
				'opening_hours' => [
					'index' => 'opening_hours',
					'type' => 'textarea',
					'label' => __('Opening Hours'),
					'value' => ['user', 'opening_hours'],
				],
				'description' => [
					'index' => 'description',
					'type' => 'tinymce',
					'label' => __('Overview'),
					'value' => ['user', 'description'],
				],
			],
		];
	}
}
