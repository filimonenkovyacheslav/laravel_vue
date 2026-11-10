<?php

namespace App\Http\Models\Emails;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
	public $fillable = [
		'name', 'title', 'lang_id', 'from_address', 'from_name', 'to_address', 'to_name', 'subject', 'greeting', 'body', 'signature',
	];

	/**
     * Get a template
     */
    public static function get($name)
    {
    	return static::query()->where('name', '=', $name)->get()->first()->toArray();
    }

    public static function saveTemplate($data)
    {
    	$name = $data['name'];
    	if(!empty($name)) {
    		$template = static::query()->where('name', '=', $name)->get()->first();
    		if($template) {
				return $template->fill($data)->save();
    		} else {
    			return static::fill($data)->save();
    		}
    	}
    }

    public static function getTitles()
    {
    	$titles = [];
    	foreach(EmailTemplate::select(['name', 'title'])->orderBy('id')->get() as $i => $template) {
    		$titles[$template->name] = $template->title;
    	}
    	return $titles;
    }

	public static function _getFieldsList() {
		return [
			'from_address' => [
				'section' => 'address',
				'type' => 'text',
				'label' => __('From Address'),
				'value' => ['template', 'from_address'],
			],
			'from_name' => [
				'section' => 'address',
				'type' => 'text',
				'label' => __('From Name'),
				'value' => ['template', 'from_name'],
			],
			'to_address' => [
				'section' => 'address',
				'type' => 'text',
				'label' => __('To Address'),
				'value' => ['template', 'to_address'],
			],
			'to_name' => [
				'section' => 'address',
				'type' => 'text',
				'label' => __('To Name'),
				'value' => ['template', 'to_name'],
			],
			'subject' => [
				'section' => 'body',
				'type' => 'text',
				'label' => __('Subject'),
				'value' => ['template', 'subject'],
			],
			'greeting' => [
				'section' => 'body',
				'type' => 'textarea',
				'label' => __('Greeting'),
				'value' => ['template', 'greeting'],
			],
			'body' => [
				'section' => 'body',
				'type' => 'textarea',
				'label' => __('Body'),
				'value' => ['template', 'body'],
				'style' => 'height: 200px;',
			],
			'signature' => [
				'section' => 'body',
				'type' => 'textarea',
				'label' => __('Signature'),
				'value' => ['template', 'signature'],
			],
		];
	}
}