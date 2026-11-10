<?php

namespace App\Http\Models\Agents;

use Illuminate\Database\Eloquent\Model;
use Agency;

class Agent extends \App\Http\Models\BaseModel
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'lang_id',
		'company_name',
		'position',
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

	public static $type = 'agent';
	public static $tableName = 'agents';
	public static $key = 'user_id';

	public static $translatable = [
		'company_name', 'position', 'description', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'holiday', 'services'
	];

	public static $selectable = [
		'company_name', 'position', 'license', 'description', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'holiday', 'services'
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

	public function agency()
	{
		return $this->belongsTo(Agency::class);
	}

	public static function getCount()
	{
		return static::join('users', 'agents.user_id', '=', 'users.id')
			->where([['users.status', 1], ['lang_id', static::getDefaultLang()]])
			->count();
	}

	public static function _getFieldsList() {
		$fields = [
			'relation' => [
				'position' => [
					'index' => 'position',
					'type' => 'text',
					'label' => __('Title / Position'),
					'value' => ['user', 'position'],
				],
				'license' => [
					'index' => 'license',
					'type' => 'text',
					'label' => __('License'),
					'value' => ['user', 'license'],
				],
				'description' => [
					'index' => 'description',
					'type' => 'tinymce',
					'label' => __('About me'),
					'value' => ['user', 'description'],
				],
				'company_name' => [
					'index' => 'company_name',
					'type' => 'text',
					'label' => __('Company Name'),
					'value' => ['user', 'company_name'],
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
