<?php

namespace App\Http\Models\Uploads;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use BaseModel;
use CustomLaravelLocalization;
use DB;
use SearchHelper;
use Property;
use Country;

class UploadProperty extends Model
{
	public $timestamps = false;

	protected $table = 'uploads_properties';
	
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
    protected $fillable = [
		'property_id',
		'upload_id',
	];

	public static function getAll($params = [], $orderBy = 'l.id', $order = 'asc')
	{
		$orderBy = !empty($orderBy) ? $orderBy : 'l.id';
		$order = !empty($order) ? $order : 'asc';

		$list = 'l.id, l.property_id, l.upload_id, u.type, u.name, properties.title, properties.slug, properties.address, properties.map_address, properties.country, properties.state, properties.city, properties.status';
		$entities = Property::select(DB::raw($list))
			->join('uploads_properties as l', 'l.property_id', '=', 'properties.id')
			->join('uploads as u', 'u.id', '=', 'l.upload_id')
			->where('u.is_featured', 1);
		
		if(isset($params['impression_type']) && $params['impression_type'] == 2) {
			$entities->whereRaw('EXISTS(SELECT 1 FROM uploads_properties ls INNER JOIN uploads us ON (us.id=ls.upload_id) WHERE ls.property_id=properties.id AND us.type=2 LIMIT 1)');
		}

		if(!empty($params)) {
			// dd($params);
			$prefix = 'properties.';

			$entities = SearchHelper::applyCommonSearchParams($entities, $params);

			foreach($params as $k => $v) {
				switch($k) {
					case 'property_status':
					case 'property_type': case 'property_subtype': case 'property_rent_schedule':
					case 'bedrooms': case 'bathrooms':
						$entities = SearchHelper::applyWhere($entities, $prefix . $k, $v);
						break;
					case 'price':
						if(array_filter($v)) {
							$entities = SearchHelper::applyPropertyPriceParam($entities, $prefix . $k . '_default', $v, $params['currency_code']);
						}
						break;
					case 'property_area':
						if(array_filter($v)) {
							$entities = SearchHelper::applyWhereBetween($entities, $prefix . $k . '_default', $v);
						}
						break;
					case 'features':
						$entities = SearchHelper::applyPropertyFeaturesParam($entities, $prefix . 'id', $params['features']);
						break;
					default:
						break;
				}
			}
		}
		//$entities = Property::_addTranslation($entities->orderBy($orderBy, $order), true)->get();
		//$pagination = BaseModel::getPageData($entities, $params, $orderBy, $order, 'UploadProperty');
		//dd(Property::_addTranslation($entities->orderBy($orderBy, $order), true)->toSql());
		$pagination = Property::_addTranslation($entities->orderBy($orderBy, $order), true)->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity, true);
		});
		return $pagination;
	}

	public static function getById($id)
	{
		$entities = Property::select(DB::raw('l.id, l.upload_id, u.type, u.name, properties.title, properties.slug, properties.address, properties.status'))
			->join('uploads_properties as l', 'l.property_id', '=', 'properties.id')
			->join('uploads as u', 'u.id', '=', 'l.upload_id')
			->where('l.id', $id);
		$entity = Property::_addTranslation($entities, true)->first();
		if($entity) {
			$entity = static::_afterGet($entity)->toArray();
		}

		return $entity;
	}

	public static function _afterGet($entity, $addAll = false) {

		$entity = Property::_replaceLangFields($entity);
		$address = '';
		if(!is_null($entity['address']) && !empty($entity['address'])) {
			$address = $entity['address'];
		}
		if(!is_null($entity['map_address']) && strlen($entity['map_address']) > strlen($address)) {
			$address = $entity['map_address'];
		}
		if(empty($address)) {
			if(!is_null($entity['city']) && !empty($entity['city'])) {
				$address = $entity['city'];
			}
			if(!is_null($entity['state']) && !empty($entity['state'])) {
				$address .= (empty($address) ? '' : ', ').$entity['state'];
			}
			if(!is_null($entity['country']) && !empty($entity['country'])) {
				$country = Country::find($entity['country']);
				if($country) {
					$address .= (empty($address) ? '' : ', ').$country->name;
				}
			}
		}
		$entity['address_view'] = $address;

		if($addAll) {
			$entities = static::select(DB::raw('u.id, u.type, u.name'))
				->join('uploads as u', 'u.id', '=', 'uploads_properties.upload_id')
				->where('uploads_properties.property_id', $entity->property_id)
				->orderBy('is_featured', 'desc')->get();
			if($entities)
			$entity['uploads'] = ($entities ? $entities->toArray() : null);
		}
		return $entity;
	}

	public static function getSortOrder($request)
	{
		$orderData = is_array($request) && !empty($request['order_by']) ? $request['order_by'] : (!empty($request->order_by) ? $request->order_by : null);

		switch($orderData) {
			case 'a_date';
				$orderBy = [
					'order_by' => 'properties.updated_at',
					'order' => 'asc',
				];
				break;
			case 'd_date';
				$orderBy = [
					'order_by' => 'properties.updated_at',
					'order' => 'desc',
				];
				break;
			case 'title';
				$orderBy = [
					'order_by' => 'title',
					'order' => 'asc',
				];
				break;
			default:
				$orderBy = [
					'order_by' => null,
					'order' => null,
				];
				break;
		}
		return $orderBy;
	}
}
