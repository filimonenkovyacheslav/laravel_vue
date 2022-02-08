<?php

namespace App\Http\Models;

use App\Http\Models\Tags\ProductCategory;
use App\Http\Models\Tags\WineCategory;
use App\Http\Models\Tags\FurnitureCategory;
use App\Http\Models\Tags\GoodCategory;
use Illuminate\Database\Eloquent\Model;
use CustomLaravelLocalization;
use Cache;
use DB;
use Validator;
use SearchHelper;
use Property;
use Franchise;
use Agency;
use Agent;
use Profession;
use Professional;
use Route;
use Art;
use News;
use Illuminate\Pagination\LengthAwarePaginator;
use Country;

class BaseModel extends Model
{
	public static $pagination = 15;
	public static $lastEntityQuery = false;
	public static $addressFields = ['country', 'address', 'city', 'state', 'postal_code', 'region', 'suburb', 'street', 'house'];

	public static function saveLastEntityQuery($query, $select = false) {
		$str = str_replace('%', '<=>', $query->toSql());
		if ($select) {
			$str = str_replace('select * from', 'select ' . $select . ' from', $str);
		}
		$str = vsprintf(str_replace('?', '%s', $str), collect($query->getBindings())->map(function ($binding) {
        	$binding = addslashes($binding);
        	return is_numeric($binding) ? $binding : "'{$binding}'";
    	})->toArray());
    	static::$lastEntityQuery = str_replace('<=>', '%', $str);
    	//dd($str, static::$lastEntityQuery);
	}

	public static function getLangFieldsList($fields, $defPrefix, $translatable, $langPrefix, $fieldPrefix = '') {
		$list = '';
		$defPrefix .= '.';
		$langPrefix .= '.';

		foreach($fields as $field) {
			$list .= (in_array($field, $translatable) ? 'COALESCE('.$langPrefix.$field.','.$defPrefix.$field.') as '.$fieldPrefix.$field : $defPrefix.$field).',';
		}
		return empty($list) ? $list : substr($list, 0, -1);
	}

	public static function replaceQuery($query, $translatable, $defPrefix, $langPrefix, $fieldPrefix = '') {
		if(!empty($defPrefix)) {
			 $defPrefix .= '.';
		}
		if(!empty($langPrefix)) {
			$langPrefix .= '.';
		}

		if(isset($query->getQuery()->orders)) {
			foreach($query->getQuery()->orders as $i => $order) {
				if(isset($order['column']) && in_array($order['column'], $translatable)) {
					$query->getQuery()->orders[$i]['column'] = $fieldPrefix.$order['column'];
				}
			}
		}
		foreach($query->getQuery()->wheres as $i => $where) {
			if(isset($where['column']) && in_array($where['column'], $translatable)) {
				$query->getQuery()->wheres[$i]['column'] = 'COALESCE('.$langPrefix.$where['column'].','.$defPrefix.$where['column'].')';
			} else if(isset($where['query'])) {
				foreach($where['query']->wheres as $j => $query_where) {
					if(isset($query_where['column']) && in_array($query_where['column'], $translatable)) {
						$query->getQuery()->wheres[$i]['query']->wheres[$j]['column'] = DB::raw('COALESCE('.$langPrefix.$query_where['column'].','.$defPrefix.$query_where['column'].')');
					}
				}
			} else if(isset($where['sql'])) {
				$sql = $where['sql'];
				foreach($translatable as $field) {
					$sql = str_replace('"'.$field.'"', 'COALESCE('.$langPrefix.$field.','.$defPrefix.$field.')', $sql);
				}
				$query->getQuery()->wheres[$i]['sql'] = $sql;
			}
		}
		return $query;
	}

	public static function findOrCreate($id)
	{
		$obj = static::find($id);
		return $obj ?: new static;
	}

	public static function getEntity($params = []) {
		return static::getEntities($params, '', false, true);
	}

	public static function setUserAddressToEntity($data, $user) {
		foreach (static::$addressFields as $field) {
			if (empty($data[$field])) {
				if ($field == 'country') {
					$data['country'] = isset($user['country_id']) ? $user['country_id'] : (empty($user['country']) ? 0 : Country::getCountryIdByName($user['country']));
				} else {
					$data[$field] = $user[$field];
				}
			}
		}
		/*$data['country'] = isset($user['country_id']) ? $user['country_id'] : (empty($user['country']) ? 0 : Country::getCountryIdByName($user['country']));
		$data['address'] = $user['address'];
		$data['city'] = $user['city'];
		$data['state'] = $user['state'];
		$data['postal_code'] = $user['postal_code'];
		$data['region'] = $user['region'];
		$data['suburb'] = $user['suburb'];
		$data['street'] = $user['street'];
		$data['house'] = $user['house'];*/
		return $data;
	}

	public static function getEntities($params = [], $orderBy = 'id', $withPagination = false, $one = false, $return = [])
	{
		$modelTable = static::$tableName;
		$tableKey = static::$key;

		$defPrefix = 'ef';
		$langPrefix = 'et';
		$defLang = BaseModel::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		$translatable = static::$translatable;

		$query = static::query();
        if ($modelTable == 'product_categories') {
            return ProductCategory::getEntities($params, $orderBy, $withPagination, $one, $return);
        }elseif ($modelTable == 'wine_categories') {
            return WineCategory::getEntities($params, $orderBy, $withPagination, $one, $return);
        }elseif ($modelTable == 'furniture_categories') {
            return FurnitureCategory::getEntities($params, $orderBy, $withPagination, $one, $return);
        }elseif ($modelTable == 'good_categories') {
            return GoodCategory::getEntities($params, $orderBy, $withPagination, $one, $return);
        }elseif(!empty($orderBy)) {
			$query->orderBy(in_array($orderBy, $translatable) ? $orderBy : $defPrefix.'.'.$orderBy);
		}

		foreach($params as $k => $v) {
			if(!in_array($k, ['page', 'order_by'])) {
				if($k == 'whereIn' && is_array($v)) {
					foreach($v as $whereKey => $whereVal) {
						$query->whereIn($defPrefix.'.'.$whereKey, $whereVal);
					}
				} else if($k == 'name') {
					$query->where($k, 'ilike', '%'.$v.'%');
				} else {
					$query->where((in_array($k, $translatable) ? $k : $defPrefix.'.'.$k), $v);
				}
			}
		}

		$query->from($modelTable.' as '.$defPrefix)
			->select(DB::raw(BaseModel::getLangFieldsList(static::$selectable, $defPrefix, ($defLang == $langId ? [] : $translatable), $langPrefix)))
			->where($defPrefix.'.lang_id', $defLang);

        if($defLang != $langId) {
        	$query = static::replaceQuery($query, $translatable, $defPrefix, $langPrefix);

        	$query->leftJoin($modelTable.' as '.$langPrefix, function ($join) use($defPrefix, $langPrefix, $langId, $tableKey){
           		$join->on($langPrefix.'.'.$tableKey, '=', $defPrefix.'.'.$tableKey)
                	->where($langPrefix.'.lang_id', '=', $langId);
        	});
        }
        
        if(!empty($return)) {
			foreach($return as $k => $v) {
				$entities = $query->$k($v);
			}
		} else if($one) {
        	$entities = $query->first();
        } else {
        	if($withPagination) {
            	$entities = $query->paginate(static::$pagination);
				$entities->getCollection()->transform(function ($entity) {
					return static::_afterGet($entity);
				});
			} else {
				$entities = $query->get();
				$entities = $entities ? $entities->toArray() : null;
			}
		}
		return $entities;
	}

	public static function _afterGet($entity, $role = null) {
		return $entity;
	}

	public static function saveItem($request, $data = [], $redirect = true)
	{
		if(empty($data)) {
			$validator = Validator::make($request->all(), static::$saveValidate);
			if($validator->fails()) {
				return redirect()->back()->withInput()->withErrors($validator);
			}
			$data = $request->toArray();
			unset($data['lang_id']);
		}

		$key = static::$key;
		$id = (isset($data[$key]) && !empty($data[$key]) ? $data[$key] : null);

		$defLang = static::getDefaultLang();
		$langId = isset($data['lang_id']) ? $data['lang_id'] : CustomLaravelLocalization::getLocaleCode();

		if(is_null($id)) {
			$maxId = DB::table(static::$tableName)->max($key);
			$id = $maxId + 1;
			$data[$key] = $id;
		}

		if($langId != $defLang) {
			$entity = static::where([[$key, $id], ['lang_id', $defLang]])->first();
			if(!$entity) {
				$entity = new static;
				$data['lang_id'] = $defLang;
				$entity->fill($data)->save();
			}
		}

		$entity = static::where([[$key, $id], ['lang_id', $langId]])->first();
		if(!$entity) {
			$entity = new static;
			$data['lang_id'] = $langId;
		}
		$entity->fill($data)->save();

		if($redirect) {
			return isset(static::$listRoute) ? redirect(route(static::$listRoute)) : redirect()->back();
		}
		return $entity;
	}

	public static function getSortOrder($request)
	{
		$orderData = is_array($request) && !empty($request['order_by']) ? $request['order_by'] : (!empty($request->order_by) ? $request->order_by : null);

		switch($orderData) {
			case 'a_price';
				$orderBy = [
					'order_by' => 'price_default',
					'order' => 'asc',
				];
				break;
			case 'd_price';
				$orderBy = [
					'order_by' => 'price_default',
					'order' => 'desc',
				];
				break;
			case 'a_date';
				$orderBy = [
					'order_by' => 'updated_at',
					'order' => 'asc',
				];
				break;
			case 'd_date';
				$orderBy = [
					'order_by' => 'updated_at',
					'order' => 'desc',
				];
				break;
			case 'title';
			case 'a_title';
				$orderBy = [
					'order_by' => 'title',
					'order' => 'asc',
				];
				break;
			case 'd_title';
				$orderBy = [
					'order_by' => 'title',
					'order' => 'desc',
				];
				break;
			case 'featured';
				$orderBy = [
					'order_by' => 'featured',
					'order' => 'asc',
				];
				break;
			case 'a_quotes_id';
				$orderBy = [
					'order_by' => 'quotes_id',
					'order' => 'asc',
				];
				break;
			case 'd_quotes_id';
				$orderBy = [
					'order_by' => 'quotes_id',
					'order' => 'desc',
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

	public static function getFields($item, $id = null)
	{
		$fields = static::_getFieldsList();
		
		return $fields;
	}

	public static function geCounterData()
	{
		/*return [
			'properties' => [
				'label' => __('Properties'),
				'icon' => '/images/home_icons/Properties.png',
				'count' => Property::where('status', '=', '1')->count(),
			],
			'agencies' => [
				'label' => __('Agencies'),
				'icon' => '/images/home_icons/Agencies.png',
				'count' => Agency::getCount(),
			],
			'agents' => [
				'label' => __('Agents'),
				'icon' => '/images/home_icons/Agents.png',
				'count' => Agent::getCount(),
			],
			'professionals' => [
				'label' => __('Professionals'),
				'icon' => '/images/home_icons/Professionals.png',
				'count' => Professional::getCount(),
			],
			'arts' => [
				'label' => __('Arts'),
				'icon' => '/images/home_icons/Properties.png',
				'count' => Art::where('status', '=', '1')->count(),
			],
		];*/
		return [];
	}

	public static function createLangJsFile()
	{
		$i18n = Cache::rememberForever('lang.js', function () {
			$langs = static::getDomainsList();
			$strings = [];

			foreach($langs as $l) {
				$lang = $l['locale'];
				$file  = glob(resource_path('lang/' . $lang . '.json'));
				$filePath = array_shift($file);

				if(!empty($filePath)) {
					$json = file_get_contents($filePath);
					$strings[$lang] = json_decode($json, true);
				}
			}
			return $strings;
		});
		header('Content-Type: text/javascript');
		echo('window.i18n = ' . json_encode($i18n) . ';');
		exit();
	}

	public static function createJsVarsFile($request)
	{
		$obj = [
			// get locale from url so it must pass through the LaravelLocalization middleware
			'curLang' => CustomLaravelLocalization::getCurrentLocale($request),
			'routesList' => static::getRoutesList()
		];
		header('Content-Type: text/javascript');
		echo('window.jsvars = ' . json_encode($obj) . ';');
		exit();
	}

	public static function getRoutesList()
	{
		$routesCollection = Route::getRoutes();
		$routesList = [];

		foreach($routesCollection as $route) {
			$name = $route->getName();

			if(!empty($name)) {
				$url = config('app')['localization_type'] == 1 ? static::_replaceLocaleFromRoute($route->uri()) : $route->uri();
				$routesList[$name] = '/' . preg_replace('/^\//i', '', $url);
			}
		}
		return $routesList;
	}

	public static function getDomainsList($withRedirect = true)
	{
		$domains = config('domain-zones');
		$appConfig = config('app');
		$domainPrefix = $appConfig['dev_mode'] ? 'dev.' : '';
		$protocol = request()->secure() ? 'https://' : 'http://';

		foreach($domains as $k => $v) {
			if($withRedirect || empty($v['redirect'])) {
				$locale = CustomLaravelLocalization::getLocaleFromDomain($v['domain']);
				$domains[$k]['link'] = $appConfig['localization_type'] == 1
					? static::_replaceLocaleFromUrl(route('home')) . $locale
					: $protocol . $domainPrefix . $v['domain'];
			} else {
				unset($domains[$k]);
			}
		}
		usort($domains, function($a, $b) {
			return strnatcmp($a['country_name'], $b['country_name']);
		});
		
		return $domains;
	}

	public static function getCurrentDomain($domains = [])
	{
		$appConfig = config('app');
		if($appConfig['localization_type'] == 1) {
			$locale = CustomLaravelLocalization::getCurrentLocale();
		} else {
			$domain = CustomLaravelLocalization::getDomainData();
			$locale = $domain['locale'];
		}

		if(empty($locale) || $locale == 'en') {
			$locale = 'com';
		}

		if(empty($domains)) {
			$domains = config('domain-zones');
		}
		foreach($domains as $k => $v) {
			if($v['locale'] == $locale) {
				return ['code' => $locale, 'name' => $v['country_name']];
			}
		}
		return ['code' => $locale, 'name' => 'Default'];
	}

	public static function _replaceLocaleFromRoute($url)
	{
		return  preg_replace('/^[a-z]+(\/|$)/i', '', $url);
	}

	public static function _replaceLocaleFromUrl($url) {
		return  preg_replace('/\/[a-z]+$/', '/', $url);
	}

	public static function getDefaultLang() {
		return config('app')['default_lang'];
	}

	public static function getSiteLogoName($isAdmin = false) {
		$suff = $isAdmin ? 'blue' : 'white';
		$defLogo = "/images/logo-small-$suff.png";
		return $defLogo;
	}

	public static function getSiteCountryName() {
		$countryName = '';

		if(config('app')['localization_type'] == 1) {
			$currentLocale = CustomLaravelLocalization::getCurrentLocale();

			$domains = config('domain-zones');

			foreach($domains as $k => $v) {
				if($currentLocale == $v['locale']) {
					$countryName = $v['country_native'];
				}
			}
		} else {
			$domainData = CustomLaravelLocalization::getDomainData();
			$countryName = $domainData['country_native'];
		}

		return $countryName;
	}
}
