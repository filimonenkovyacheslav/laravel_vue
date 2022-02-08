<?php

namespace App\Http\Models\Wines;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Auth;
use DB;
use Currency;
use CurrencyConverter;
use Money;
use CustomLaravelLocalization;
use Validator;
use User;
use Upload;
use SearchHelper;
use ElasticSearchHelper;
use Country;
use Role;
use PropertyPrice;
use WineLang;
use WineFavorite;
use WineCategory;
use AddressKeyword;

class Wine extends \App\Http\Models\BaseModel
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
    
    public $fillable = [
        'title', 'slug', 'author', 'status', 'label',
        'price_default', 'price_local',
        'price_second', 'currency_code', 'price_before', 'price_after', 'price_hidden',
        'description', 'video', 'house', 'street', 'suburb', 'region',
        'address', 'postal_code', 'country', 'state', 'city', 'neighborhood', 'map_address', 'lat', 'lng', 'position'
    ];
    
    public $casts = [
        'price_hidden' => 'boolean',
    ];
    public static $type = 'wine';
    public static $tableName = 'wines';
    public static $langTable = 'wine_langs';
    public static $langKey = 'wine_id';
    public static $langFields = 'wine_langs';
    public static $defaultImage = 'post-logo.jpg';
    
    
    public static function getWineData($key = '') {
        $data = [
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
        ];
        
        return empty($key) ? $data : $data[$key];
    }
    
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
    
    public static function countWines() {
        $items = static::query();
        
        return [
            [
                'title' => 'Wines',
                'count' => $items->count(),
                'published' => $items->where('status', 1)->count()
            ]
        ];
    }
    
    public static function _addTranslation($query, $keepSelect = false)
    {
        $defLang = static::getDefaultLang();
        $langId = CustomLaravelLocalization::getLocaleCode();
        if($langId == $defLang) return $query;
        
        $defTable = self::$tableName;
        $langTable = self::$langTable;
        $fieldPrefix = 'lang_';
        $translatable = static::$translatable;
        
        $query = static::replaceQuery($query, $translatable, $defTable, $langTable, $fieldPrefix);
        
        if(!$keepSelect) {
            $query->select($defTable.'.*');
        }
        
        $query->leftJoin($langTable, function ($join) use($defTable, $langTable, $langId) {
            $join->on($langTable.'.'.self::$langKey, '=', $defTable.'.id')
                ->where($langTable.'.lang_id', '=', $langId);
        })
            ->addSelect(DB::raw(static::getLangFieldsList($translatable, $defTable, $translatable, $langTable, $fieldPrefix)));
        
        return $query;
    }
    
    public static function _canWineEdit($entity, $adminOnly = false) {
        $user = Auth::user();
        $admin = $user->isAdmin();
        if($adminOnly && !$admin) return false;
        
        if(!$entity || !isset($entity->author)) {
            return false;
        }
        
        if(!$admin && $user->id != $entity->author) {
            return false;
        }
        
        return true;
    }
    
    public static function getAll($params = [], $with = [], $orderBy = 'id', $order = 'asc')
    {
        $defaultOrder = is_null($orderBy) || empty($orderBy);
        $orderBy = !empty($orderBy) ? $orderBy : 'price_default';
        $order = !empty($order) ? $order : 'desc';
        
        $entities = static::query();
        $prefix = self::$tableName . '.';
        //dd($params);
        if(!empty($params)) {
            $entities->select($prefix . '*');
            $entities = SearchHelper::applyCommonSearchParams($entities, $params);
            
            foreach($params as $k => $v) {
                switch($k) {
                    case 'mprice':
                        if(array_filter($v)) {
                            $entities = SearchHelper::applyPropertyPriceParam($entities, $prefix . 'price_default', $v, $params['currency_code']);
                            //dd($entities->toSql());
                        }
                        break;
                    case 'author':
                        $v = (int) trim($v);
                        $entities = $entities->where($prefix . 'author', $v);
                        break;
                    default:
                        break;
                }
            }
        }
        
        if (!isset($params['category'])) {
            $categories = WineCategory::getAllListParent();
            if (!empty($categories)) {
                $categories = array_keys($categories);
                $categories = implode(',', $categories);
                $entities = $entities->whereRaw("{$prefix}id IN (SELECT wine_id FROM wine_category_relation WHERE wine_category_id IN ({$categories}))");
            }
        }
        
        $entities->where($prefix . 'status', 1);
        static::saveLastEntityQuery($entities, self::$tableName . '.id');
        $entities->with($with);
        if ($defaultOrder) {
            $entities->orderBy('wines.label', 'asc')->orderBy('position', 'asc')->orderBy($orderBy, $order);
        } else {
            $entities->orderBy($orderBy, $order)->orderBy('wines.label', 'asc')->orderBy('position', 'asc');
        }
        
        $pagination = static::_addTranslation($entities)->paginate(static::$pagination);
        $pagination->getCollection()->transform(function ($entity) {
            return static::_afterGet($entity);
        });
        return $pagination;
    }
    
    public static function getByParam($param, $value, $with = ['user'])
    {
        $entity = static::_addTranslation(static::where(self::$tableName . '.' . $param, $value))->with($with)->first();
        $entity = static::_afterGet($entity);
        $entity['user'] = isset($entity['user']) ? User::_afterGet($entity['user'], isset($entity['user']['type']) ? $entity['user']['type'] : null, 1) : null;
        if ($entity['user'] && isset($entity['user']['email']) && in_array($entity['user']['email'], User::$userEmailsToChange)) {
            $entity['user']['email'] = 'info@medicaleer.com';
        }
        
        return $entity;
    }

    public static function cloneItem($data) {
        $data['id'] = null;
        $data['slug'] = null;
        unset($data['created_at'], $data['updated_at']);
        
        $data['categories'] = is_array($data['categories']) ? implode(',', $data['categories']) : '';
        $data['photos'] = [];
        if (is_array($data['uploadsList'])) {
            foreach ($data['uploadsList'] as $upload) {
                $upId = Upload::cloneUpload($upload['id']);
                if ($upId) {
                    $data['photos'][] = $upId;
                }
            }
        }
        //dd($data);
        $item = static::saveItem($data, true);
        return $item ? $item['id'] : 0;
    }
    
    public static function saveItem($request, $preset = false, $langsData = []) {
        $data = !$preset ? $request->all() : $request;
        //dd($data);
        $validator = Validator::make($data, [
            'title' => 'required',
            'price' => 'required|numeric|min:1',
            'categories' => 'required',
            'house' => 'max:50',
            'street' => 'max:100',
            'suburb' => 'max:100',
            'region' => 'max:100',
        ])->setAttributeNames([
            'title' => __('Title'),
            'price' => __('Price'),
            'categories' => __('Item Categories'),
            'house' => __('House'),
            'street' => __('Street'),
            'suburb' => __('Suburb'),
            'region' => __('Region'),
        ]);
        
        if($validator->fails()) {
            return ['errors' => $validator->errors()->toArray()];
        }
        
        $id = (isset($data['id']) && !empty($data['id']) ? $data['id'] : null);
        $new = is_null($id);
        $entity = static::findOrCreate($id);
        
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        if(!$preset && !$isAdmin && $data['author'] != $user->id) {
            return ['message' => __('This is not your wine.'), 'errors' => []];
        }
        $author = $data['author'] == $user->id ? $user : User::find($data['author']);
        if (!$author) {
            return ['message' => __('Author not found.'), 'errors' => []];
        }
        
        if(!$preset && !$new && !static::_canWineEdit($entity)) {
            return redirect(url('/'));
        }
        
        $data['slug'] = null;
        $data['price_hidden'] = !empty($data['price_hidden']);
        $data = PropertyPrice::calculatePrice($data);
        
        $defLang = static::getDefaultLang();
        $langId = $preset && isset($data['langId']) ? $data['langId'] : CustomLaravelLocalization::getLocaleCode();
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
        if ($isAdmin && $new) {
            $data = static::setUserAddressToEntity($data, $author);
        }

        $entity->fill($data);
        $entity->save();
        $id = $entity->id;
        if(isset($langsData) && sizeof($langsData) > 0) {
            foreach($langsData as $langId => $langData) {
                $lang = WineLang::where([[self::$langKey, $id], ['lang_id', $langId]])->first();
                if(!$lang) {
                    $langData[self::$langKey] = $id;
                    $langData['lang_id'] = $langId;
                    $lang = new WineLang();
                }
                $lang->fill($langData)->save();
                ElasticSearchHelper::updateElasticEntity($lang, self::$tableName);
            }
        } else {
            ElasticSearchHelper::updateElasticEntity($entity, self::$tableName);
        }
        Upload::saveUploadedImages(isset($data['photos']) ? $data['photos'] : [], $id, static::$tableName);
        Upload::makeImageFeatured($id, static::$tableName, isset($data['featured_image']) ? $data['featured_image'] : null);
        
        if ($isAdmin || $new) {
            if ($new && (empty($data['keywords']) || !$isAdmin)) {
                $data['keywords'] = AddressKeyword::getEntityKeywords($entity->author, 'user');
            }
            AddressKeyword::saveEntityKeywords(isset($data['keywords']) ? $data['keywords'] : [], $id, static::$type);
        }
        
        $entity = $entity->toArray();
    
        $categories = explode(',', $data['categories']);
        $entity['categories'] = WineCategory::saveWineCategories($id, $categories);
        
        if(!$preset) {
            //$user = User::find($entity['author']);
            $entity['author_name'] = $author->first_name.' '.$author->last_name.' (ID '.$author->id.')';
        }
        
        return $entity;
    }
    
    public static function updateItem($id, $request, $preset = false) {
        $data = !$preset ? $request->all() : $request;
        if (isset($data['id'])) {
            unset($data['id']);
        }
        $entity = static::find($id);
        if (empty($entity)) {
            return false;
        }
        
        if (isset($data['price'])) {
            $data = PropertyPrice::calculatePrice($data);
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
    
    public static function deleteWineById($id)
    {
        $entity = static::find($id);
        if(!static::_canWineEdit($entity, false)) {
            return redirect(url('/'));
        }
        $user = Auth::user();
        if ($user->isAdmin()) {
            $entity->delete();
            
            WineCategory::deleteWineCategories($id);
        } else {
            static::setWineStatus($id, 5);
        }
        return true;
    }
    
    public static function setWineStatus($id, $status)
    {
        $entity = static::find($id);
        if(!static::_canWineEdit($entity, $status == 6)) {
            return redirect(url('/'));
        }
        if(!static::_canWineEdit($entity, $status == 1)) {
            return redirect(url('/'));
        }
        if($entity->status != $status) {
            $entity->fill(['status' => $status])->save();
            return $entity->toArray();
        }
        return true;
    }
    
    public static function setWineLabel($id, $label)
    {
        $entity = static::find($id);
        
        if(!static::_canWineEdit($entity, true)) {
            return redirect(url('/'));
        }
        
        $labels = static::getWineData('label');
        $labelId = isset($labels[$label]) ? $labels[$label]['id'] : null;
       
        if($entity->label != $labelId) {
            $entity->fill(['label' => $labelId])->save();
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
            $entityData['is_favorite'] = WineFavorite::isWineFeatured($id);
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
            $entityData['fields'] = WineCategory::_getWineFieldsList();
            $entityData['categories'] = WineCategory::getWineCategories($id);
            $entityData = static::_replaceLangFields($entityData);
            $wineData = static::getWineData();
            foreach($wineData as $k => $d) {
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
            $entityData['keywords'] = AddressKeyword::getEntityKeywords($id, static::$type);
        } else {
            $user = Auth::user();
            if(!$user) return [];
            $isAdmin = $user->isAdmin();

            $user =  User::_afterGet($user);

            $entityData = [
                'lang_id' => CustomLaravelLocalization::getLocaleCode(),
                'status' => ($isAdmin ? 1 : 2),
                'author' => $user['id'],
                'currency_code' => PropertyPrice::$defaultCurrencyCode,
                'fields' => WineCategory::_getWineFieldsList(),
                'categories' => [],
                'user' => [],
                'keywords' => []
            ];
            if (!$isAdmin) {
                $entityData['country'] = $user['country_id'];
                $entityData['address'] = $user['address'];
                $entityData['city'] = $user['city'];
                $entityData['state'] = $user['state'];
                $entityData['postal_code'] = $user['postal_code'];
                $entityData['region'] = $user['region'];
                $entityData['suburb'] = $user['suburb'];
                $entityData['street'] = $user['street'];
                $entityData['house'] = $user['house'];
            }
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
    
    public static function searchWineForLocations($keyword, $limit = 15)
    {
        $keyword = strpos($keyword, ',') !== false ? explode(',', $keyword)[0] : $keyword;
        $value = '%'.$keyword.'%';
        $entities = static::where('city', 'ilike', $value)
            ->orWhere('state', 'ilike', $value)
            //->orWhereRaw(DB::raw('(map_address ilike \'%'.$keyword.'%\' and (city ilike \'%'.$keyword.'%\' or state ilike \'%'.$keyword.'%\'))'))
            ->orWhereRaw("(map_address ilike ? and (city ilike ? or state ilike ?))", [$value, $value, $value])
            ->orderBy('city', 'asc')
            ->orderBy('lng', 'asc')
            ->limit(100000)->get();
        $results = [];
        $cities = [];
        if($entities) {
            foreach($entities as $entity) {
                $city = !empty($entity->city) ? $entity->city : $entity->state;
                $tmpCity = mb_strtolower($city . $entity->country);
                if (in_array($tmpCity, $cities)) {
                    continue;
                }
                $cities[] = $tmpCity;
                $state = $entity->state && $entity->state != $city ? ', ' . $entity->state : '';
                $country = $entity->country ? ', ' . Country::getCountryName($entity->country) : '';
                $results[] = [
                    'id' => $entity->id, 'name' => $city . $state . $country,
                    'iso2' => null, 'iso3' => null, 'lat' => $entity->lat, 'lng' => $entity->lng,
                    'city' => $city, 'state' => $entity->state, 'country' => $entity->country
                ];
                
                if (count($results) >= $limit) {
                    break;
                }
            }
        }
        return $results;
    }
    
    public static function getWineFilters( $params = [] )
    {
        return [];
    }
}
