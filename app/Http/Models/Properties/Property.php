<?php

namespace App\Http\Models\Properties;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use PropertyPrice;
use PropertyMeasures;
use Auth;
use DB;
use Currency;
use CurrencyConverter;
use Money;
use Measure;
use CustomLaravelLocalization;
use Validator;
use User;
use Upload;
use Feature;
use SearchHelper;
use ElasticSearchHelper;
use Country;
use Role;
use AgencyAgents;
use PropertiesFloors;
use PropertyCategory;
use AddressKeyword;

class Property extends \App\Http\Models\BaseModel
{
	use Sluggable;
	
	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'title'
			]
		];
	}

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'title', 'slug', 'author', 'status', 'label',
		'price_default', 'price_local',
		'price_second', 'currency_code', 'price_before', 'price_after', 'price_hidden',
		'description', 'property_type', 'property_subtype', 'property_status', 'property_rent_schedule',
		'property_area_local', 'property_area_default', 'property_area_measure',
		'land_area_local', 'land_area_default', 'land_area_measure',
		'garage', 'garage_area_local', 'garage_area_default', 'garage_area_measure',
		'bedrooms', 'bathrooms', 'year_built', 'video',
		'address', 'postal_code', 'country', 'state', 'city', 'neighborhood', 'map_address', 'lat', 'lng'
	];
	public $casts = [
		'price_hidden' => 'boolean',
	];
	public static $type = 'property';
	public static $tableName = 'properties';
	public static $langTable = 'property_langs';
	public static $langKey = 'property_id';
	public static $langFields = 'property_langs';
	public static $defaultImage = 'post-logo.jpg';


	public static function getPropertyData($key = '') {
		$propertyData = [
			'status' => [
				'publish' => ['id' => 1, 'label' => __('Published')],
				'pending' => ['id' => 2, 'label' => __('Pending')],
				'expired' => ['id' => 3, 'label' => __('Expired')],
				'draft' => ['id' => 4, 'label' => __('Draft')],
				'deleted' => ['id' => 5, 'label' => __('Deleted')],
				'unpublished' => ['id' => 6, 'label' => __('Unpublished')],
			],
			'label' => [
				'premium' => ['id' => 1, 'label' => __('Premium'), 'color' => 'blue'],
				'featured' => ['id' => 2, 'label' => __('Featured'), 'color' => 'orange'],
				'sold' => ['id' => 3, 'label' => __('Sold'), 'color' => 'red'],
			],
			'property_status' => [
				['id' => 1, 'slug' => 'for_rent', 'label' => __('For Rent')],
				['id' => 2, 'slug' => 'for_sale', 'label' => __('For Sale')],
				['id' => 3, 'slug' => 'foreclosure', 'label' => __('Foreclosure')],
				['id' => 4, 'slug' => 'project_design', 'label' => __('Project / Design')],
				['id' => 5, 'slug' => 'sold', 'label' => __('Sold')],
			],
			'property_rent_schedule' => [
				['id' => 1, 'slug' => 'daily', 'label' => __('Daily')],
				['id' => 2, 'slug' => 'weekly', 'label' => __('Weekly')],
				['id' => 3, 'slug' => 'monthly', 'label' => __('Monthly')],
				['id' => 4, 'slug' => 'yearly', 'label' => __('Yearly')],
			],
			'property_type' => [
				['id' => 1, 'label' => __('Apartment')],
				['id' => 2, 'label' => __('House')],
				['id' => 3, 'label' => __('Room')],
				['id' => 4, 'label' => __('Temporary')],
				['id' => 5, 'label' => __('Flatmate')],
				['id' => 6, 'label' => __('Commercial')],
				['id' => 7, 'label' => __('Retirement Living')],
				['id' => 8, 'label' => __('Vacation Home')],
				['id' => 9, 'label' => __('Townhouse')],
				['id' => 10, 'label' => __('Project Home')],
				['id' => 11, 'label' => __('Land')],
				['id' => 12, 'label' => __('Island')],
				['id' => 13, 'label' => __('Insolvency Property')],
				['id' => 14, 'label' => __('Vineyard')],
				['id' => 15, 'label' => __('Other')]
			],
			'property_subtype' => [
				['id' => 1, 'label' => __('Offices')],
				['id' => 2, 'label' => __('Shared Workspaces')],
				['id' => 3, 'label' => __('Retail')],
				['id' => 4, 'label' => __('Industrial / Warehouse')],
				['id' => 5, 'label' => __('Showrooms')],
				['id' => 6, 'label' => __('Land / Development')],
				['id' => 7, 'label' => __('Hotel / Gastronomy')],
				['id' => 8, 'label' => __('Medical')],
				['id' => 9, 'label' => __('Farms')],
				['id' => 10, 'label' => __('Other')],
			],
		];

		return empty($key) ? $propertyData : $propertyData[$key];
	}

	public static $propertyTypeLinks = [
		'status_types' => [
			1 => [1, 2, 3, 4, 5, 6, 7, 8, 15], //for_rent

			2 => [1, 2, 6, 9, 10, 11, 12, 13, 7, 14, 15], //for_sale
		],
		'type_status_subtypes' => [
			6 => [
				1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], //Commercial -> for_rent
				2 => [1, 3, 4, 5, 6, 7, 8, 9, 10], //Commercial -> for_sale
			],
		],
	];

	public static $hasArea = [
		'property_area', 'land_area', 'garage_area',
	];
	public static $translatable = [
		'title', 'description', 'address',
	];

	public function getDescriptionAttribute($value) {
		return !empty($value) ? $value : '';
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'author');
	}

	public function country()
    {
      return $this->belongsTo(Country::class, 'country');
    }

	public static function _addTranslation($query, $keepSelect = false)
	{
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		if($langId == $defLang) return $query;

		$defTable = 'properties';
		$langTable = 'property_langs';
		//$defPrefix = $defTable.'.';
		//$langPrefix = $langTable.'.';
		$fieldPrefix = 'lang_';
		$translatable = static::$translatable;

		$query = static::replaceQuery($query, $translatable, $defTable, $langTable, $fieldPrefix);

		if(!$keepSelect) {
			$query->select($defTable.'.*');
		}

		$query->leftJoin($langTable, function ($join) use($defTable, $langTable, $langId) {
				$join->on($langTable.'.property_id', '=', $defTable.'.id')
					->where($langTable.'.lang_id', '=', $langId);
			})
			->addSelect(DB::raw(static::getLangFieldsList($translatable, $defTable, $translatable, $langTable, $fieldPrefix)));

		return $query;
	}

	public static function _canPropertyEdit($property, $adminOnly = false) {
		$user = Auth::user();
		$admin = $user->isAdmin();
		if($adminOnly && !$admin) return false;

		if(!$property || !isset($property->author)) {
			return false;
		}

		if(!$admin && $user->id != $property->author && AgencyAgents::getAgencyId($property->author) != $user->id) {
			return false;
		}

		/*if(!$property || !isset($property->author) || ($user->id != $property->author && !$admin)) {
			return false;
		}*/
		return true;
	}

	public static function getAll($params = [], $with = [], $orderBy = 'id', $order = 'asc')
	{
		$defaultOrder = is_null($orderBy) || empty($orderBy);
		$orderBy = !empty($orderBy) ? $orderBy : 'price_default';
		$order = !empty($order) ? $order : 'desc';

		$entities = static::query();

		if(!empty($params)) {
			$prefix = 'properties.';
			$entities->select($prefix . '*');
			$entities = SearchHelper::applyCommonSearchParams($entities, $params);

			foreach($params as $k => $v) {
				switch($k) {
					case 'property_status':
					case 'property_type': case 'property_subtype': case 'property_rent_schedule':
					case 'bedrooms': case 'bathrooms':
						if(is_numeric($v)) {
							$entities = SearchHelper::applyWhere($entities, $prefix . $k, $v);
						}
						break;
					case 'price':
						if(array_filter($v)) {
							$entities = SearchHelper::applyPropertyPriceParam($entities, $prefix . $k . '_default', $v, $params['currency_code']);
						}
						break;
					case 'property_area':
						if(array_filter($v)) {
							$entities = SearchHelper::applyPropertyAreaParam($entities, $prefix . $k . '_default', $v, $params['measure_code']);
						}
						break;
					case 'features':
						$entities = SearchHelper::applyPropertyFeaturesParam($entities, $prefix . 'id', $params['features']);
						break;
					default:
						break;
				}
			}
			//dd($params, $entities->toSql(), $entities->get());
		}
		$entities->where('status', 1)->with($with);
		static::saveLastEntityQuery($entities, self::$tableName . '.id');
		$entities = static::_addTranslation($entities);
		if ($defaultOrder) {
            //$entities->orderBy('properties.label', 'asc')->orderBy($orderBy, $order);
            $entities->select('properties.*')->addSelect(DB::raw('(EXISTS(SELECT 1 FROM property_address_keywords WHERE property_id = properties.id)) as set_loc'));
            $entities->orderBy('set_loc', 'desc')->orderByRaw('(CASE WHEN properties.label=3 THEN NULL ELSE properties.label END)')->orderBy($orderBy, $order);
            //$entities->orderBy('set_loc', 'desc')->orderBy('properties.label', 'asc')->orderBy($orderBy, $order);
        } else {
            $entities->orderBy($orderBy, $order);
        }
        //dd($entities->toSql());
		$pagination = $entities->paginate(static::$pagination);
		//$pagination = static::_addTranslation($entities->where('status', 1)->with($with)->orderBy($orderBy, $order)->orderBy('properties.label', 'asc'))->paginate(static::$pagination);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity);
		});
		return $pagination;
	}

	public static function getByParam($param, $value, $with = ['user'])
	{
		$entity = static::_addTranslation(static::where('properties.'.$param, $value))->with($with)->first();
		$entity = static::_afterGet($entity);
		//dd($entity);
		$entity['user'] = isset($entity['user']) ? User::_afterGet($entity['user'], isset($entity['user']['type']) ? $entity['user']['type'] : null, 1) : null;
        if ($entity['user'] && isset($entity['user']['email']) && in_array($entity['user']['email'], User::$userEmailsToChange)) {
            $entity['user']['email'] = 'info@medicaleer.com';
        }
        
		return $entity;
	}

	public static function saveItem($request, $preset = false, $langsData = []) {
		$data = !$preset ? $request->all() : $request;
		$validator = Validator::make($data, [
			'title' => 'required',
			'price' => 'required|numeric|min:1',
		])->setAttributeNames([
			'title' => __('Title'),
			'price' => __('Price'),
		]);
		if($validator->fails()) {
			return ['errors' => $validator->errors()->toArray()];
		}
		$id = (isset($data['id']) && !empty($data['id']) ? $data['id'] : null);
		$new = is_null($id);
		$entity = static::findOrCreate($id);

		$user = Auth::user();
		$isAdmin = $preset ? true : $user->isAdmin();
		if (!$preset && !$isAdmin && $data['author'] != $user->id) {
			if(AgencyAgents::getAgencyId($data['author']) != $user->id) {
				return ['message' => __('This is not your agent.'), 'errors' => []];
			}
		}
		$author = !$preset && $data['author'] == $user->id ? $user : User::find($data['author']);
        if (!$author) {
            return ['message' => __('Author not found.'), 'errors' => []];
        }

		if(!$preset && !$new && !static::_canPropertyEdit($entity)) {
			return redirect(url('/'));
		}

		$data['slug'] = null;
		// Boolean fields
		$data['price_hidden'] = !empty($data['price_hidden']);
		// Additional actions
		$data = PropertyPrice::calculatePrice($data);
		$data = PropertyMeasures::calculateMeasures($data);

		$defLang = static::getDefaultLang();
		$langId = $preset && isset($data['langId']) ? $data['langId'] : CustomLaravelLocalization::getLocaleCode();
		//dd($data, $id, $entity);
		if(!$preset && $defLang != $langId) {
			$langData = [];
			foreach(static::$translatable as $field) {
				if(isset($data[$field])) {
					$langData[$field] = $data[$field];
					if(!$new) {
						unset($data[$field]);
					}
				}
			}
			if(sizeof($langData) > 0) {
				$langsData[$langId] = $langData;
			}
		}
		/*if ($isAdmin && $new) {
            $data = static::setUserAddressToEntity($data, $author);
        }*/
		//dd($defLang, $langId, $langData);
		$entity->fill($data);
		$entity->save();
		$id = $entity->id;
		if(isset($langsData) && sizeof($langsData) > 0) {
			foreach($langsData as $langId => $langData) {
				$lang = PropertyLang::where([['property_id', $id], ['lang_id', $langId]])->first();
				if(!$lang) {
					$langData['property_id'] = $id;
					$langData['lang_id'] = $langId;
					$lang = new PropertyLang;
				}
				$lang->fill($langData)->save();
				ElasticSearchHelper::updateElasticEntity($lang, 'properties');
			}
		} else {
			ElasticSearchHelper::updateElasticEntity($entity, 'properties');
		}
		Upload::saveUploadedImages(isset($data['photos']) ? $data['photos'] : [], $id, static::$tableName);
		Upload::makeImageFeatured($id, static::$tableName, isset($data['featured_image']) ? $data['featured_image'] : null);
		Feature::savePropertyFeatures($id, isset($data['features']) ? $data['features'] : []);
		PropertiesFloors::savePropertyFloors($id,  isset($data['floors']) ? $data['floors'] : []);

		if ($isAdmin || $new) {
            if ($new && (empty($data['keywords']) || !$isAdmin)) {
                $data['keywords'] = AddressKeyword::getEntityKeywords($entity->author, 'user');
            }
            AddressKeyword::saveEntityKeywords(isset($data['keywords']) ? $data['keywords'] : [], $id, static::$type);
        }

		$entity = $entity->toArray();

		$categories = empty($data['categories']) ? [] : explode(',', $data['categories']);
        $entity['categories'] = PropertyCategory::savePropertyCategories($id, $categories);

		if(!$preset/* && Auth::user()->isAdmin()*/) {
			$entity['author_name'] = $author->first_name.' '.$author->last_name.' (ID '.$author->id.')';
		}
		//dd($entity);

		return $entity;
	}
    
    /**
     * Update property table params only
     *
     * @param       $id
     * @param       $request
     * @param bool  $preset
     *
     * @return bool
     */
    public static function updateItem($id, $request, $preset = false) {
        $data = !$preset ? $request->all() : $request;
        if (isset($data['id'])) {
            unset($data['id']);
        }
        $entity = static::find($id);
        if (empty($entity)) {
            return false;
        }
        // Additional actions
        if (isset($data['price'])) {
            $data = PropertyPrice::calculatePrice($data);
        }
        if (array_key_exists('property_area', $data) ||
            array_key_exists('land_area', $data) ||
            array_key_exists('garage_area', $data)) {
            $data = PropertyMeasures::calculateMeasures($data);
        }
        $defLang = static::getDefaultLang();
        $langId = $preset && isset($data['langId']) ? $data['langId'] : CustomLaravelLocalization::getLocaleCode();
        if(!$preset && $defLang != $langId) {
            foreach(static::$translatable as $field) {
                if(isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }
        
        $entity->fill($data);
        $entity->save();
        $id = $entity->id;
        
        return $id;
    }

	public static function deletePropertyById($id)
	{
		$property = static::find($id);
		if(!static::_canPropertyEdit($property, false)) {
			return redirect(url('/'));
		}
		$user = Auth::user();
		if ($user->isAdmin()) {
			$property->delete();
			PropertyCategory::deletePropertyCategories($id);
		} else {
			static::setPropertyStatus($id, 5);
		}
		return true;
	}

	public static function setPropertyStatus($id, $status)
	{
		$property = static::find($id);
		if(!static::_canPropertyEdit($property, $status == 6)) {
			return redirect(url('/'));
		}
		if(!static::_canPropertyEdit($property, $status == 1)) {
			return redirect(url('/'));
		}
		if($property->status != $status) {
			$property->fill(['status' => $status])->save();
			return $property->toArray();
		}
		return true;
	}

	public static function setPropertyLabel($id, $label)
	{
		$property = static::find($id);

		if(!static::_canPropertyEdit($property, true)) {
			return redirect(url('/'));
		}
		$labels = static::getPropertyData('label');
		$labelId = isset($labels[$label]) ? $labels[$label]['id'] : null;

		if($property->label != $labelId) {
			$property->fill(['label' => $labelId])->save();
		}
		return true;
	}

	public static function _afterGet($entity, $role = null, $relation = null)
	{
		$entityData = $entity;

		if(!empty($entityData)) {
			$entityData = !is_array($entityData) ? $entityData->toArray() : $entityData;

			if(isset($entityData['user']) && is_array($entityData['user'])) {
				$user = $entityData['user'];
				$role = Role::find($user['role_id'])->name;
				$entityData['user'] = array_merge($user, User::getUserRelation($user['id'], $role));

				$entityData['user']['type'] = $role;
				$entityData['author_name'] = $user['first_name'].' '.$user['last_name'].' (ID '.$user['id'].')';
			}

			$id = $entityData['id'];
			$entityData['is_favorite'] = PropertyFavorite::isPropertyFeatured($id);
			$entityData['features'] = Feature::getPropertyFeaturesIds($id);
			$entityData['featuresList'] = Feature::getFeaturesDataByParam('feature_id', $entityData['features']);
			$entityData['uploadsList'] = Upload::getUploadedImages($id, static::$tableName);
            if (empty($entityData['uploadsList'])) {
                $entityData['uploadsList'][] = [
                    'id' => 0,
                    'name' => static::$defaultImage,
                    'type' => 1
                ];
            }
			$entityData['uploadsTypes'] = [];
			foreach($entityData['uploadsList'] as $key => $upload) {
				if(!in_array($upload['type'], $entityData['uploadsTypes'])) {
					array_push($entityData['uploadsTypes'], $upload['type']);
				}
                if (!empty($upload['name'])) {
                    $entityData['uploadsList'][$key]['name'] = url('/uploads'). '/'. $upload['name'];
                }
			}
			$entityData['fields'] = PropertyCategory::_getPropertyFieldsList(false);
            $entityData['categories'] = PropertyCategory::getPropertyCategories($id);

			$entityData['floors'] = PropertiesFloors::getPropertyFloors($id);
			$entityData = static::_replaceLangFields($entityData);
			$propertyData = static::getPropertyData();
			foreach($propertyData as $k => $d) {
				$entityData[$k . '_view'] = [];
				if(!empty($entityData[$k])) {
					foreach($d as $v) {
						if($v['id'] == $entityData[$k]) {
							$entityData[$k . '_view'] = $v;
						}
					}
				}
			}
			$entityData = PropertyPrice::preparePriceToView($entityData);
			$entityData = PropertyMeasures::prepareMeasureToView($entityData);
			$entityData['keywords'] = AddressKeyword::getEntityKeywords($id, static::$type); 
		} else {
			$user = Auth::user();
			if(!$user) return [];
			$measure = CustomLaravelLocalization::getDefaultAreaMeasureCode();
			$entityData = [
				'lang_id' => CustomLaravelLocalization::getLocaleCode(),
				'status' => ($user->isAdmin() ? 1 : 2),
				'author' => $user->id,
				'property_status' => '',
				'property_type' => '',
				'property_subtype' => '',
				'property_rent_schedule' => '',
				'currency_code' => PropertyPrice::$defaultCurrencyCode,
				'property_area_measure' => $measure,
				'land_area_measure' => $measure,
				'garage_area_measure' => $measure,
				'fields' => PropertyCategory::_getPropertyFieldsList(),
                'categories' => [],
				'user' => [],
				'keywords' => []
			];
		}
		return $entityData;
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
    
    public static function searchPropertyForLocations($keyword, $limit = 15)
    {
        $keyword = strpos($keyword, ',') !== false ? explode(',', $keyword)[0] : $keyword;
        /*$properties = static::where('city', 'ilike', '%'.$keyword.'%')
            ->orWhere('state', 'ilike', '%'.$keyword.'%')
            ->orWhereRaw(DB::raw('(map_address ilike \'%'.$keyword.'%\' and (city ilike \'%'.$keyword.'%\' or state ilike \'%'.$keyword.'%\'))'))
            ->orderBy('city', 'asc')
            ->orderBy('lng', 'asc')
            ->limit(100000)->get();*/
        $quoted = DB::getPdo()->quote('%' . $keyword .'%');
        //$properties = static::whereRaw('(city ilike ' . $quoted . ' OR state ilike ' . $quoted . ' OR map_address ilike ' . $quoted . ')')
        $properties = static::whereRaw("(city ilike ? OR state ilike ? OR map_address ilike ?)", [$quoted, $quoted, $quoted])
            ->orderBy('city', 'asc')
            ->orderBy('lng', 'asc')
            ->limit(100000)->get();

        $results = [];
        $cities = [];
        if($properties) {
            foreach($properties as $property) {
                $city = !empty($property->city) ? $property->city : $property->state;
                $tmpCity = mb_strtolower($city . $property->country);
                if (in_array($tmpCity, $cities)) {
                    continue;
                }
                $cities[] = $tmpCity;
                $state = $property->state && $property->state != $city ? ', ' . $property->state : '';
                $country = $property->country ? ', ' . Country::getCountryName($property->country) : '';
                $results[] = [
                    'id' => $property->id, 'name' => $city . $state . $country,
                    'iso2' => null, 'iso3' => null, 'lat' => $property->lat, 'lng' => $property->lng,
                    'city' => $city, 'state' => $property->state, 'country' => $property->country
                ];
                
                if (count($results) >= $limit) {
                    break;
                }
            }
        }
        return $results;
    }
    public static function countProperties() {
        $items = static::query();
        
        return [
            [
                'title' => 'Properties',
                'count' => $items->count(),
                'published' => $items->where('status', 1)->count()
            ]
        ];
    }
}
