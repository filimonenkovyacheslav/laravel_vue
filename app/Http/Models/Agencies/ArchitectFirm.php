<?php

namespace App\Http\Models\Agencies;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class ArchitectFirm extends \App\Http\Models\BaseModel
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
		'description',
		'monday',
		'tuesday',
		'wednesday',
		'thursday',
		'friday',
		'saturday',
		'sunday',
		'holiday',
		'services',
	];

	public static $type = 'architect_firm';
	public static $tableName = 'architect_firms';
	public static $key = 'user_id';

	public static $translatable = [
		'company_name', 'description', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'holiday', 'services'
	];

	public static $selectable = [
		'company_slug', 'company_name', 'license', 'description', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'holiday', 'services'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public static function getOpeningFields() {
		return [
			'monday' => __('Monday'),
			'tuesday' => __('Tuesday'),
			'wednesday' => __('Wednesday'),
			'thursday' => __('Thursday'),
			'friday' => __('Friday'),
			'saturday' => __('Saturday'),
			'sunday' => __('Sunday'),
			'holiday' => __('Public Holiday'),
		];
	}

	public static function _getFieldsList() {
		$fields = [
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
				'description' => [
					'index' => 'description',
					'type' => 'tinymce',
					'label' => __('Overview'),
					'value' => ['user', 'description'],
				],
				'services' => [
					'index' => 'services',
					'type' => 'tinymce',
					'label' => __('Products & Services'),
					'value' => ['user', 'services'],
				],
			],
		];
		foreach (static::getOpeningFields() as $key => $value) {
			$fields['relation'][$key] = [
				'index' => $key,
				'type' => 'text',
				'label' => $value,
				'maxlength' => 99,
				'value' => ['user', $key],
			];
		}
		return $fields;
	}
}
