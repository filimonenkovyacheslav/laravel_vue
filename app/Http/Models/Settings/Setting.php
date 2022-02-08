<?php

namespace App\Http\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use EmailTemplate;

class Setting extends Model
{
	public $fillable = [
		'section', 'name', 'value',
	];

	public static $settings = null;

	/**
     * Get a settings value
     */
    public static function getValue($section, $name, $default = null)
    {
    	$setting = static::where('section', '=', $section)->where('name', '=', $name)->first();

        if($setting) {
        	return $setting->value;
        }

        return static::getDefaultValue($section, $name, $default);
    }

    /**
     * Get a settings values
     */
    public static function getValuesLike($section, $name, $default = null)
    {
        $settings = static::where('section', $section)->where('name', 'ilike', $name)->get();

        return $settings;
    }

    /**
     * Get a random settings values
     */
    public static function getRandomValueLike($section, $name)
    {
        $setting = static::where('section', $section)->where('name', 'ilike', $name)->inRandomOrder()->first();

        if($setting) {
            return $setting->value;
        }
    }

    /**
     * Get a settings values by section
     */
    public static function getValuesBySection($section, $default = true)
    {
    	$settings = [];
    	foreach(static::getAllSettings()->where('section', '=', $section) as $i => $setting) {
    		$settings[$setting->name] = $setting->value;
    	}
        if(!$default) return $settings;

        if(sizeof($settings) > 0) {
        	return array_merge(static::getDefaultValuesBySection($section), $settings);
        }

        return static::getDefaultValuesBySection($section);
    }

    /**
     * Get default value from config if no value passed
     *
     * @param $name
     * @param $default
     * @return mixed
     */
    private static function getDefaultValue($section, $name, $default)
    {
        return is_null($default) ? config($section.'.'.$name) : $default;
    }

    /**
     * Get default values for Section
     *
     * @param $section
     * @param $default
     * @return mixed
     */
    private static function getDefaultValuesBySection($section)
    {
        return config($section);
    }

    /**
     * Set a value for setting
     *
     * @param $section
     * @param $name
     * @param $value
     * @return bool
     */
    public static function setValue($section, $name, $value)
    {
    	$setting = static::where([['section', $section], ['name', $name]])->first();
    	if($setting) {
			return $setting->fill(['value' => $value])->save();
    	} else {
    		return static::create(['section' => $section, 'name' => $name, 'value' => $value]);
    	}
    }

    /**
     * Save values for settings
     *
     * @param $section
     * @param array $data
     * @return bool
     */
    public static function saveSection($section, $data = array())
    {
    	foreach(static::_getFieldsList($section) as $name => $setting) {
    		static::setValue($section, $name, isset($data[$name]) ? $data[$name] : '');
    	}
        return true;
    }

    /**
     * Delete settings values
     */
    public static function deleteSettings($section, $name, $like = false)
    {
        $settings = static::where('section', $section);
        if($like) {
            $settings->where('name', 'ilike', $name);
        } else {
            $settings->where('name', $name);
        }
        $settings->delete();
    }

    /**
     * Get all the settings
     *
     * @return mixed
     */
    public static function getAllSettings()
    {
    	if(is_null(static::$settings)) {
    		static::$settings = self::all();
    	}
        return static::$settings;
    }

    public static function _getFieldsList($section) {
		$fields = [
			'emails' => [
				'driver' => [
					'subsection' => 'settings',
					'type' => 'selectbox',
					'label' => __('Driver'),
					'value' => ['emails', 'driver'],
					'options' => [''=> 'Default', 'smtp' => 'smtp'],
					'disabled' => 0,
				],
				'host' => [
					'subsection' => 'settings',
					'type' => 'text',
					'label' => __('Host'),
					'value' => ['emails', 'host'],
				],
				'port' => [
					'subsection' => 'settings',
					'type' => 'text',
					'label' => __('Port'),
					'value' => ['emails', 'port'],
				],
				'username' => [
					'subsection' => 'settings',
					'type' => 'text',
					'label' => __('User Name'),
					'value' => ['emails', 'username'],
				],
				'password' => [
					'subsection' => 'settings',
					'type' => 'text',
					'label' => __('Password'),
					'value' => ['emails', 'password'],
				],
				'encryption' => [
					'subsection' => 'settings',
					'type' => 'selectbox',
					'label' => __('Encryption'),
					'value' => ['emails', 'encryption'],
					'options' => ['tls' => 'tls', 'ssl' => 'ssl'],
				],
			],
			'job_entity' => [
					'job_user_add' => [
						'subsection' => 'settings',
						'type' => 'checkbox',
						'label' => __('Can a user create a job'),
						'value' => ['job_entity', 'job_user_add'],
					],
					'job_limit' => [
						'subsection' => 'settings',
						'type' => 'number',
						'label' => __('User job posts limit'),
						'value' => ['job_entity', 'job_limit'],
					],
			],
		];
		$result = $fields[$section];
		if($section == 'emails') {
			$templates = EmailTemplate::all()->toArray();
			foreach($templates as $data) {
				$name = $data['name'];
				$result[$name] = [
					'subsection' => 'templates',
					'type' => 'checkbox',
					'label' => $data['title'],
					'button' => url(route('admin.emails.template', ['params' => $name])),
					'value' => ['emails', $name],
				];
			}
		}
		return $result;
	}
}
