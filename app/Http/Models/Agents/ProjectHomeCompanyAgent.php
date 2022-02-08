<?php

namespace App\Http\Models\Agents;

use Illuminate\Database\Eloquent\Model;

class ProjectHomeCompanyAgent extends \App\Http\Models\BaseModel
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
		'opening_hours',
		'description',
	];

	public static $type = 'project_home_company_agent';
	public static $tableName = 'project_home_company_agents';
	public static $key = 'user_id';

	public static $translatable = [
		'company_name', 'position', 'description', 'opening_hours',
	];

	public static $selectable = [
		'company_name', 'position', 'license', 'description', 'opening_hours',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public static function _getFieldsList() {
		return [
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
				'opening_hours' => [
					'index' => 'opening_hours',
					'type' => 'textarea',
					'label' => __('Opening Hours'),
					'value' => ['user', 'opening_hours'],
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
			],
		];
	}
}
