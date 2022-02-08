<?php

namespace App\Http\Models\Agencies;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use User;
use Agent;

class Agency extends \App\Http\Models\BaseModel
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

	public static $type = 'agency';
	public static $tableName = 'agencies';
	public static $key = 'user_id';

	public static $translatable = [
		'company_name', 'description', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'holiday', 'services'
	];

	public static $selectable = [
		'company_slug', 'company_name', 'license', 'description', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'holiday', 'services'
	];

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

	public function user()
    {
      return $this->belongsTo(User::class);
    }

	public function agents()
	{
		return $this->hasMany(Agent::class, 'agency_id');
	}

	public static function getCount()
	{
		return static::join('users', 'agencies.user_id', '=', 'users.id')
			->where([['users.status', 1], ['lang_id', static::getDefaultLang()]])
			->count();
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
					'label' => __('About Agency'),
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
