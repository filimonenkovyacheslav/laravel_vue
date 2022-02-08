<?php

namespace App\Http\Models\News;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Auth;
use DB;
use CustomLaravelLocalization;
use Validator;
use User;
use Upload;
use UploadNews;
use SearchHelper;
use ElasticSearchHelper;
use Country;
use Role;
use PropertyPrice;
use NewsLang;
use SimpleKeyword;
use NewsSimpleKeyword;

class News extends \App\Http\Models\BaseModel
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
        'title', 'slug', 'author', 'status', 'label', 'title_uploads_id', 'description', 'position'
    ];
    
    public $casts = [
        'price_hidden' => 'boolean',
    ];
    public static $type = 'news';
    public static $tableName = 'news';
    public static $langTable = 'news_langs';
    public static $langKey = 'news_id';
    public static $langFields = 'news_langs';
    public static $defaultImage = 'logo-profilepic.jpg';
    
    
    public static function getNewsData($key = '') {
        $artData = [
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
                /*'sold' => ['id' => 3, 'label' => __('Sold'), 'color' => 'red'],*/
            ],
        ];
        
        return empty($key) ? $artData : $artData[$key];
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
            $join->on($langTable.'.news_id', '=', $defTable.'.id')
                ->where($langTable.'.lang_id', '=', $langId);
        })
            ->addSelect(DB::raw(static::getLangFieldsList($translatable, $defTable, $translatable, $langTable, $fieldPrefix)));
        
        return $query;
    }
    
    public static function getAll($params = [], $with = [], $orderBy = 'id', $order = 'asc')
    {
        $defaultOrder = is_null($orderBy) || empty($orderBy);
        $orderBy = !empty($orderBy) ? $orderBy : 'created_at';
        $order = !empty($order) ? $order : 'desc';
        $news_arr = [];
        $news_search_word = '';
        
        $entities = static::query();
        $prefix = self::$tableName . '.';
     
        if(!empty($params)) {
            if (isset($params['news_search_word'])) {
                $news_search_word = $params['news_search_word'];
                $entities->select($prefix . '*', DB::raw('(CASE WHEN '.$prefix.'title ilike '.DB::getPdo()->quote($news_search_word).' THEN 1 WHEN '.$prefix.'description ilike '.DB::getPdo()->quote($news_search_word).' THEN 2 ELSE 3 END) AS sorter'));
            }
            else{
                $entities->select($prefix . '*');
            }            
            
            $entities = SearchHelper::applyCommonSearchParams($entities, $params);
          
            foreach($params as $k => $v) {
                switch($k) {
                    case 'author':
                        $v = (int) trim($v);
                        $entities = $entities->where($prefix . 'author', $v);
                        break;
                    case 'news_keyword': 
                        $v = (int) trim($v);
                        $news_arr = NewsSimpleKeyword::where('key_id',$v)->pluck('news_id');
                        break;
                    case 'news_search_word': 
                        $v = trim($v);
                        $news_arr = News::where('title','ilike', "%$v%")->pluck('id')->toArray();
                        $news_arr_2 = News::where('description','ilike', "%$v%")
                        ->whereNotIn('id', $news_arr)
                        ->pluck('id')->toArray();
                        $news_arr = $news_arr + $news_arr_2;
                        break;
                    default:
                        break;
                }
            }
        }
        $entities->where($prefix . 'status', 1);

        if ($news_arr) {            
            $entities->whereIn($prefix . 'id', $news_arr);
        }
        static::saveLastEntityQuery($entities, self::$tableName . '.id');
        $entities->with($with);

        if (!$news_arr) {
            if ($defaultOrder) {
                $entities->orderBy('news.label', 'asc')->orderBy('position', 'asc')->orderBy($orderBy, $order);
            } else {
                $entities->orderBy($orderBy, $order)->orderBy('news.label', 'asc')->orderBy('position', 'asc');
            }
        }
        else if ($news_search_word){
            $entities->orderBy('sorter');
        }
        
        $pagination = static::_addTranslation($entities)->paginate(static::$pagination);
        //$pagination = static::_addTranslation($entities->where('status', 1)->with($with)->orderBy($orderBy, $order)->paginate(static::$pagination);
        //

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

    public static function countNews() {
        $items = static::query();
        
        return [
            [
                'title' => 'News',
                'count' => $items->count(),
                'published' => $items->where('status', 1)->count()
            ]
        ];
    }

    public static function _canNewsEdit($entity, $adminOnly = false) {
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

    public static function saveUploadItem($request, $preset = false, $langsData = [])
    {
        $data = !$preset ? $request->all() : $request;
        //dd($data);
        $validator = Validator::make($data, [
            'title' => 'required',
        ])->setAttributeNames([
            'title' => __('Title'),
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
            return ['message' => __('This is not your news.'), 'errors' => []];
        }
        $author = $data['author'] == $user->id ? $user : User::find($data['author']);
        if (!$author) {
            return ['message' => __('Author not found.'), 'errors' => []];
        }
        
        if(!$preset && !$new && !static::_canNewsEdit($entity)) {
            return redirect(url('/'));
        }
        
        $data['slug'] = null;
        
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

        $data['file_link'] = isset($data['fileNew']) ? '' : $data['file_link'];

        $entity->fill($data);
        $entity->save();

        $entityData = $entity->toArray();
        $old_id = DB::table('news')->where('id', $entityData['id'])->first()->title_uploads_id;

        if (!empty($data['fileNew'])) { 
            
            if ($old_id) {
                UploadNews::where('upload_id', $old_id)->delete();
                Upload::deleteUpload([], $old_id);
                Upload::where('id', $old_id)->delete();
            }
                      
            $data = Upload::attachUploads($data, $request, ['fileNew'], false);
            $title_uploads_id = $data['fileNew'];
            Upload::saveUploadedImages(isset($data['fileNew']) ? $data['fileNew'] : [], $entityData['id'], static::$tableName);           
            
            $entityData['file_link'] = Upload::getUploadedItemNews($entityData['id']);
            if ($entityData['file_link']) {
                $entityData['file_link'] = '/uploads/'. trim($entityData['file_link']);
                DB::table('news')->where('id', $entityData['id'])->update([
                    'file_link' => $entityData['file_link'],
                    'title_uploads_id' => $title_uploads_id
                ]);
            }            
            
        } elseif (empty($data['file_link'])) {
            if ($old_id) {
                UploadNews::where('upload_id', $old_id)->delete();
                Upload::deleteUpload([], $old_id);
                Upload::where('id', $old_id)->delete();
                DB::table('news')->where('id', $entityData['id'])->update([
                    'file_link' => null,
                    'title_uploads_id' => null
                ]);
            }
        }

        return $entityData;
    }
    
    public static function saveItem($request, $preset = false, $langsData = []) {
        $data = !$preset ? $request->all() : $request;
        //dd($data);
        $validator = Validator::make($data, [
            'title' => 'required',
        ])->setAttributeNames([
            'title' => __('Title'),
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
            return ['message' => __('This is not your news.'), 'errors' => []];
        }
        $author = $data['author'] == $user->id ? $user : User::find($data['author']);
        if (!$author) {
            return ['message' => __('Author not found.'), 'errors' => []];
        }
        
        if(!$preset && !$new && !static::_canNewsEdit($entity)) {
            return redirect(url('/'));
        }
        
        $data['slug'] = null;
        /*$data['price_hidden'] = !empty($data['price_hidden']);
        $data = PropertyPrice::calculatePrice($data);*/
        
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

        $entity->fill($data);
        if (!$isAdmin) {
            $entity->status = 2;
        }
        $entity->save();
        $id = $entity->id;
        if(isset($langsData) && sizeof($langsData) > 0) {
            foreach($langsData as $langId => $langData) {
                $lang = NewsLang::where([[self::$langKey, $id], ['lang_id', $langId]])->first();
                if(!$lang) {
                    $langData[self::$langKey] = $id;
                    $langData['lang_id'] = $langId;
                    $lang = new NewsLang();
                }
                $lang->fill($langData)->save();
                ElasticSearchHelper::updateElasticEntity($lang, self::$tableName);
            }
        } else {
            ElasticSearchHelper::updateElasticEntity($entity, self::$tableName);
        }

        if(isset($data["keywords"]) && sizeof($data["keywords"]) > 0){
            $key_arr = [];
            NewsSimpleKeyword::where('news_id',$id)->delete();
            foreach ($data["keywords"] as $value) {
                $key_arr[] = [
                    'news_id'=>$id,
                    'key_id'=>$value['key_id']
                ];
            }
            NewsSimpleKeyword::insert($key_arr);            
        }
        
        Upload::saveUploadedImages(isset($data['photos']) ? $data['photos'] : [], $id, static::$tableName);
        Upload::makeImageFeatured($id, static::$tableName, isset($data['featured_image']) ? $data['featured_image'] : null);      
        
        $entity = $entity->toArray();
        
        if(!$preset) {
            //$user = User::find($entity['author']);
            $entity['author_name'] = $author->first_name.' '.$author->last_name.' (ID '.$author->id.')';
        }
        
        return $entity;
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
            $entityData['is_favorite'] = NewsFavorite::isNewsFeatured($id);
            $entityData['uploadsList'] = Upload::getUploadedImages($id, static::$tableName);
            $entityData['uploadsMain'] = Upload::getUploadedMain($id);
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

            /*if (empty($entityData['uploadsMain'])) {
                $entityData['uploadsMain'][] = [
                    'id' => 0,
                    'name' => static::$defaultImage,
                    'type' => 1
                ];
            }*/
            $entityData['uploadsMainTypes'] = [];
            foreach($entityData['uploadsMain'] as $key => $upload) {
                if(!in_array($upload['type'], $entityData['uploadsMainTypes'])) {
                    array_push($entityData['uploadsMainTypes'], $upload['type']);
                }
                if (!empty($upload['name'])) {
                    $entityData['uploadsMain'][$key]['name'] = url('/uploads'). '/'. $upload['name'];
                }
            }
            /*$entityData['fields'] = WineCategory::_getWineFieldsList();
            $entityData['categories'] = WineCategory::getWineCategories($id);*/
            $entityData = static::_replaceLangFields($entityData);
            $newsData = static::getNewsData();
            foreach($newsData as $k => $d) {
                $entityData[$k . '_view'] = [];
                if(!empty($entityData[$k])) {
                    foreach($d as $v) {
                        if($v['id'] == $entityData[$k]) {
                            $entityData[$k . '_view'] = $v;
                        }
                    }
                }
            }
            //$entityData = PropertyPrice::preparePriceToView($entityData);
            $entityData['keywords'] = SimpleKeyword::getEntityKeywords($id, static::$type);
        } else {
            $user = Auth::user();
            if(!$user) return [];
            $isAdmin = $user->isAdmin();

            $user =  User::_afterGet($user);

            $entityData = [
                'lang_id' => CustomLaravelLocalization::getLocaleCode(),
                'status' => ($isAdmin ? 1 : 2),
                'author' => $user['id'],
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
    
    public static function updateItem($id, $request, $preset = false) {
        $data = !$preset ? $request->all() : $request;
        if (isset($data['id'])) {
            unset($data['id']);
        }
        $entity = static::find($id);
        if (empty($entity)) {
            return false;
        }
        
        /*if (isset($data['price'])) {
            $data = PropertyPrice::calculatePrice($data);
        }*/
        
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
    
    public static function deleteNewsById($id)
    {
        $entity = static::find($id);
        if(!static::_canNewsEdit($entity, false)) {
            return redirect(url('/'));
        }
        $user = Auth::user();
        if ($user->isAdmin()) {
            //$entity->delete();
            static::setNewsStatus($id, 5);
        } else {
            static::setNewsStatus($id, 5);
        }
        return true;
    }
    
    public static function setNewsStatus($id, $status)
    {
        $entity = static::find($id);
        if(!static::_canNewsEdit($entity, $status == 6)) {
            return redirect(url('/'));
        }
        if(!static::_canNewsEdit($entity, $status == 1)) {
            return redirect(url('/'));
        }
        if($entity->status != $status) {
            $entity->fill(['status' => $status])->save();
            return $entity->toArray();
        }
        return true;
    }
    
    public static function setNewsLabel($id, $label)
    {
        $entity = static::find($id);
        
        if(!static::_canNewsEdit($entity, true)) {
            return redirect(url('/'));
        }
        
        $labels = static::getNewsData('label');
        $labelId = isset($labels[$label]) ? $labels[$label]['id'] : null;
       
        if($entity->label != $labelId) {
            $entity->fill(['label' => $labelId])->save();
        }
        return true;
    }       
    
    public static function getNewsFilters( $params = [] )
    {
        return [];
    }
}
