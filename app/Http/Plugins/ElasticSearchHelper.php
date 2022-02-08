<?php

namespace App\Http\Plugins;

use CustomLaravelLocalization;

class ElasticSearchHelper {
	public static $useElasticSearch;

	public static function isUseElasticSearch()
	{
		if(static::$useElasticSearch === null) {
			static::$useElasticSearch = config('app')['use_elastic_search'];
		}
		return static::$useElasticSearch;
	}

	public static function getElasticEntity($entity, $type)
	{
		$command = 'curl -X GET "'. static::_getBaseLink($type) .'/'. $entity->property_id .'?pretty"';
		//dd($command);
		exec($command, $output);
		return $output;
	}

	public static function addElasticEntity($entity, $type)
	{
		$data = static::_prepareDataToSearch($entity, $type);
		$command = 'curl -X POST "'. static::_getBaseLink($type) .'/'. $data['id'] .'" -d \'{ '.
			'"title": "'. $data['title'] .'",'.
			'"slug": "'. $data['slug'] .'",'.
			'"description": "'. $data['description'] .'" }\'';
		//dd($command);
		exec($command, $output);
		return $output;
	}

	public static function updateElasticEntity($entity, $type)
	{
		$output = null;

		if(static::isUseElasticSearch()) {
			$output = static::getElasticEntity($entity, $type);
			$output = json_decode(implode('', $output));

			if(!empty($output->found) && $output->found == true) {
				$data = static::_prepareDataToSearch($entity, $type);
				$command = 'curl -X PUT "'. static::_getBaseLink($type) .'/'. $data['id'] .'?pretty" -d \'{ '.
					'"title": "'. $data['title'] .'",'.
					'"slug": "'. $data['slug'] .'",'.
					'"description": "'. $data['description'] .'" }\'';
				//dd($command);
				exec($command, $output);
			} else {
				$output = static::addElasticEntity($entity, $type);
			}
		}
		return $output;
	}

	public static function deleteElasticEntity($entity, $type)
	{
		$command = 'curl -X DELETE "'. static::_getBaseLink($type) .'/'. $entity->property_id .'"';
		//dd($command);
		exec($command, $output);
		return $output;
	}

	public static function searchElastic($string, $type)
	{
		$output = null;

		if(static::isUseElasticSearch()) {
			$command = 'curl -X GET "'. static::_getBaseLink($type) .'/_search?pretty" '.
				'-d \'{"_source": [ "title", "slug" ],'.
				'"query": {"bool": {"should": [{ "match_phrase": { "title": {"query": "'. $string .'"}}},{ "match_phrase": { "description": {"query": "'. $string .'"}}}]'.
				'}}}\'';
			//dd($command);
			exec($command, $output);
			$output = is_array($output) ? json_decode(implode('', $output)) : $output;
		}
		return $output;
	}

	public static function _getBaseLink($type)
	{
		$lang = CustomLaravelLocalization::getCurrentLocale();
		return 'http://localhost:9200/'. $type .'/'. $lang;
	}

	public static function _prepareDataToSearch($entity, $type)
	{
		$data = [];
		$data['id'] = !empty($entity->property_id) ? $entity->property_id : (!empty($entity->user_id) ? $entity->user_id : $entity->id);
		$data['slug'] = !empty($entity->company_slug) ? $entity->company_slug : $entity->slug;
		$data['title'] = !empty($entity->title)
			? $entity->title
			: (!empty($entity->company_name) ? $entity->company_name
				: (!empty($entity->first_name) && !empty($entity->last_name) ? $entity->first_name . ' ' . $entity->company_name : ''));
		$data['description'] = !empty($entity->description) ? $entity->description : '';

		return $data;
	}
}