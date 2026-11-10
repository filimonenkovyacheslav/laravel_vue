<?php

namespace App\Http\Models\Properties;

use Illuminate\Database\Eloquent\Model;
use PropertyPrice;
use PropertyMeasures;
use CustomLaravelLocalization;
use PropertiesFloorsLang;
use DB;

class PropertiesFloors extends \App\Http\Models\BaseModel
{
	public static $tableName = 'properties_floors';

	public static $hasArea = ['area_size'];

	public $fillable = [
		'property_id', 'title', 'description', 'image', 'bedrooms', 'bathrooms',
		'area_size_default', 'area_size_local', 'area_size_measure',
		'price_default', 'price_local', 'currency_code',
		'sort_order',
	];

	public static $translatable = [
		'title', 'description',
	];

	public static function getPropertyFloors($propertyId) {
		$floors = static::_addTranslation(static::where('property_id', $propertyId))->orderBy('sort_order', 'asc')->get();
		$floors = !empty($floors) ? $floors->toArray() : [];
		foreach($floors as $k => $v) {
			$floors[$k] = static::_replaceLangFields($floors[$k]);
			$floors[$k] = PropertyPrice::preparePriceToView($floors[$k]);
			$floors[$k] = PropertyMeasures::prepareMeasureToView($floors[$k], 'PropertiesFloors');
			$floors[$k]['price'] = $floors[$k]['currency_code'] == PropertyPrice::$defaultCurrencyCode
				? $floors[$k]['price_default']
				: $floors[$k]['price_local'];
			$floors[$k]['area_size'] = $floors[$k]['area_size_measure'] == PropertyMeasures::$defaultMeasureCode
				? $floors[$k]['area_size_default']
				: $floors[$k]['area_size_local'];
		}
		return $floors;
	}

	public static function _addTranslation($query, $keepSelect = false)
	{
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		if($langId == $defLang) return $query;

		$defTable = static::$tableName;
		$langTable = $defTable. '_langs';
		$fieldPrefix = 'lang_';
		$translatable = static::$translatable;
		$query = static::replaceQuery($query, $translatable, $defTable, $langTable, $fieldPrefix);

		if(!$keepSelect) {
			$query->select($defTable.'.*');
		}
		$query->leftJoin($langTable, function ($join) use($defTable, $langTable, $langId) {
			$join->on($langTable.'.floor_id', '=', $defTable.'.id')
				->where($langTable.'.lang_id', '=', $langId);
		})->addSelect(DB::raw(static::getLangFieldsList($translatable, $defTable, $translatable, $langTable, $fieldPrefix)));

		return $query;
	}

	public static function _replaceLangFields($entity) {
		foreach(static::$translatable as $field) {
			$name = 'lang_'.$field;
			if(isset($entity[$name])) {
				$entity[$field] = $entity[$name];
			}
		}
		return $entity;
	}

	public static function savePropertyFloors($propertyId, $floors) {
		$curFloorsIds = static::where('property_id', $propertyId)->pluck('id');
		$curFloorsIds = !empty($curFloorsIds) ? $curFloorsIds->toArray() : [];
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		foreach($floors as $k => $f) {
			$floorItemId = (isset($f['id']) && !empty($f['id']) ? $f['id'] : null);
			$floorItem = static::findOrCreate($floorItemId);
			$floorData = array_merge($f, ['property_id' => $propertyId, 'sort_order' => ($k + 1)]);
			$floorData = PropertyPrice::calculatePrice($floorData);
			$floorData = PropertyMeasures::calculateMeasures($floorData, 'PropertiesFloors');
			$new = is_null($floorItemId);
			if(!$new) {
				$index = array_search($floorItemId, $curFloorsIds);
				array_splice($curFloorsIds, $index, 1);
			}
			if($defLang != $langId) {
				$langData = [];
				foreach(static::$translatable as $field) {
					if(isset($floorData[$field])) {
						$langData[$field] = $floorData[$field];
						if(!$new) {
							unset($floorData[$field]);
						}
					}
				}
				if(sizeof($langData) > 0) {
					$langsData[$langId] = $langData;
				}
			}
			$floorItem->fill($floorData);
			$floorItem->save();
			$floorItemId = $floorItem->id;
			if(isset($langsData) && sizeof($langsData) > 0) {
				foreach($langsData as $langId => $langData) {
					$lang = PropertiesFloorsLang::where([['floor_id', $floorItemId], ['lang_id', $langId]])->first();
					if(!$lang) {
						$langData['floor_id'] = $floorItemId;
						$langData['lang_id'] = $langId;
						$lang = new PropertiesFloorsLang;
					}
					$lang->fill($langData);
					$lang->save();
				}
			}
		}
		if(!empty($curFloorsIds)) {
			static::whereIn('id', $curFloorsIds)->delete();
			PropertiesFloorsLang::whereIn('floor_id', $curFloorsIds)->delete();
		}
	}
}
