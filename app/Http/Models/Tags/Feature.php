<?php

namespace App\Http\Models\Tags;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use CustomLaravelLocalization;
use FeatureProperty;
use DB;

class Feature extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'feature_id', 'lang_id', 'name', 'slug',
	];

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'name'
			]
		];
	}
	public static $listRoute = 'user.profile.features';
	public static $type = 'feature';
	public static $tableName = 'features';
	public static $key = 'feature_id';

	public static $saveValidate = [
		'name' => 'required',
	];
	public static $translatable = [
		'name'
	];
	public static $selectable = [
		'feature_id', 'slug', 'name',
	];

	public static function countFeatures() {
        return [
            [
                'title' => 'Features',
                'count' => static::query()->count()
            ]
        ];
    }

	public static function getFeaturesDataByParam($param, $valuesArray, $returnValue = '') {
		$valuesArray = !is_array($valuesArray) ? [$valuesArray] : $valuesArray;
		$return = !empty($returnValue) ? ['pluck' => $returnValue] : [];
		$features = static::getEntities(['whereIn' => [static::$key => $valuesArray]], 'id', false, false, $return);
		return $features;
	}

	public static function getPropertyFeaturesIds($entityId) {
		$features = DB::table('features')
			->where([
				['features_properties.property_id', '=', $entityId],
			])
			->join('features_properties', 'features_properties.feature_id', '=', 'features.feature_id')
			->join('properties', 'features_properties.property_id', '=', 'properties.id')
			->groupBy('features.feature_id')
			->pluck('features.feature_id');
		$features = !empty($features) ? $features->toArray() : [];
		return $features;
	}

	public static function savePropertyFeatures($id, $features) {
		FeatureProperty::where('property_id', $id)->delete();
		if(!empty($features)) {
			foreach ($features as $p) {
				$item = new FeatureProperty;
				$item->fill(['property_id' => $id, 'feature_id' => $p]);
				$item->save();
			}
		}
	}

	public static function _getFieldsList() {
		return [
			'feature_id' => [
				'index' => 'feature_id',
				'type' => 'hidden',
				'label' => __('Feature Id'),
				'value' => ['feature', 'feature_id'],
			],
			'name' => [
				'index' => 'name',
				'type' => 'text',
				'label' => __('Feature Title *'),
				'value' => ['feature', 'name'],
			],
			'slug' => [
				'index' => 'slug',
				'type' => 'text',
				'label' => __('Slug'),
				'value' => ['feature', 'slug'],
			],
		];
	}
}
