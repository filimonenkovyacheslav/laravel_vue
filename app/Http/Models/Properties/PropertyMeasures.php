<?php

namespace App\Http\Models\Properties;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Property;
use PropertiesFloors;
use Measure;

class PropertyMeasures extends Model
{
	public static $defaultMeasureCode = 1;

	public static function calculateMeasures($data, $model = 'Property') {
		foreach($model::$hasArea as $name) {
			$localName = $name . '_local';
			$defaultName = $name . '_default';
			if(!empty($data[$name])) {
				$area = (float) $data[$name];
				$areaMeasure = $data[$name . '_measure'];
				$isAreaInDefault = $areaMeasure == static::$defaultMeasureCode;
				if($isAreaInDefault) {
					$data[$localName] = null;
					$data[$defaultName] = $area;
				} else {
					$from = Measure::getMeasureByCode($areaMeasure);
					$to = Measure::getMeasureByCode(static::$defaultMeasureCode);
					$data[$localName] = $area;
					$data[$defaultName] = Measure::convert($area, $from, $to);
				}
			} else {
				$data[$defaultName] = $data[$localName] = null;
			}
		}
		return $data;
	}

	public static function prepareMeasureToView($entity, $model = 'Property') {
		$measures = array_change_key_case(Measure::getMeasures());

		foreach($model::$hasArea as $name) {
			$viewName = $name . '_view';
			$entity[$viewName] = $measures;
			if(!empty($entity[$name . '_default'])) {
				$measureName = $name . '_measure';

				foreach($entity[$viewName] as $k => $v) {
					if($v['code'] == static::$defaultMeasureCode) {
						$entity[$viewName][$k]['value'] = $entity[$name . '_default'];
					} else {
						if(!empty($entity[$name . '_local'])) {
							$entity[$viewName][$k]['value'] = $entity[$name . '_local'];
						} else {
							$from = Measure::getMeasureByCode(static::$defaultMeasureCode);
							$to = Measure::getMeasureByCode($v['code']);
							$entity[$viewName][$k]['value'] = Measure::convert($entity[$name . '_default'], $from, $to);
						}
					}
				}
				$entity[$name] = $entity[$measureName] == static::$defaultMeasureCode ? $entity[$name . '_default'] : $entity[$name . '_local'];
				// dd($entity[$viewName]);
			}
		}
		return $entity;
	}
}
