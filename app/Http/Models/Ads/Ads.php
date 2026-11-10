<?php

namespace App\Http\Models\Ads;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use User;
use Role;
use Upload;
use UploadsAds;
use Country;
use MenuCategoryItem;

class Ads extends \App\Http\Models\BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'title', 'url', 'keywords', 'file_link', 'country_id', 'order', 'status',
        'city', 'state', 'type', 'address'
    ];
    public static $type = 'ads';
    public static $tableName = 'ads';
    protected $primaryKey = 'ads_id';
    
    public static function getAdsData($key = '') {
        $adsData = [
            'status' => [
                'publish' => ['id' => 1, 'label' => __('Published')],
                'deleted' => ['id' => 5, 'label' => __('Deleted')],
                'unpublished' => ['id' => 6, 'label' => __('Unpublished')],
            ],
            'order_by' => [
                1 => ['id' => 'd_date', 'label' => __('New to old')],
                2 => ['id' => 'a_date', 'label' => __('Old to new')],
                3 => ['id' => 'title', 'label' => __('A-Z')],
                4 => ['id' => 'd_title', 'label' => __('Z-A')],
            ],
        ];
        
        return empty($key) ? $adsData : $adsData[$key];
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'country');
    }
    
    public static function _canAdsEdit($ads, $adminOnly = false) {
        $user = Auth::user();
        $admin = $user->isAdmin();
        if ($admin) {
            return true;
        }
        return false;
    }
    
    public static function getByParam($param, $value, $additional = [], $status = null)
    {
        if ($param == 'all') {
            !is_array($value) && $value = [$value];
            empty($value) && $value = array_merge($value, $additional);
            if (!empty($additional)) {
                $value = array_merge($value, $additional);
            }

            $page_type = (array_key_exists('search_type', $value))? $value['search_type']:'';

            $entity = Ads::query();
            foreach ($value as $name => $item) {
                if (empty($item)) {continue;}
                switch ($name) {
                    case 'keyword':
                    $item = strtolower($item);
                    $ad_ids = DB::table('ads_keywords')->where('key_hash', md5($item))->get(['ads_id'])->toArray();
                    $ad_ids = !empty($ad_ids) ? array_column($ad_ids, 'ads_id') : [0];
                        //dd($ad_ids);
                    $entity = $entity->whereIn('ads.ads_id', $ad_ids);
                    break;
                    case 'ai':
                    $item = strtoupper($item);
                    $country = Country::where('iso2', $item)->first();
                    if($country) {
                        $entity = $entity->where('ads.country_id', $country->id);
                    }
                    break;
                    case 'ac':
                    $entity = $entity->where('ads.city', $item);
                    break;
                    case 'as':
                    $entity = $entity->where('ads.state', $item);
                    break;
                    case 'search_location':
                    if (strlen($item) >= 3) {
                        $entity = $entity->whereRaw("ads.address ILIKE ?", [DB::getPdo()->quote('%'.$item.'%')]);
                    }
                    break;
                    case 'search_type':
                    if (isset($value['search_location'])) {
                        $entity = $entity->where('ads.type', $item);
                    } else {
                        $entity = $entity->orWhereRaw("(ads.type = ?) AND (ads.address IS NULL)", [$item]);
                    }
                    break;
                    default:
                    break;
                }
            }
            
        } elseif ($param == 'keywords') {
            $value = strtolower($value);
            $ad_ids = DB::table('ads_keywords')->where('key_hash', md5($value))->get(['ads_id'])->toArray();
            $ad_ids = !empty($ad_ids) ? array_column($ad_ids, 'ads_id') : [0];
            $entity = static::whereIn('ads.ads_id', $ad_ids);
        } else {
            $entity = static::where('ads.'.$param, $value);
        }
        if(!is_null($status)) {
            $entity->where('status', $status);
        }

        $entity = $entity->inRandomOrder()->take(1)->get()->toArray();
        $entity = !empty($entity) ? array_shift($entity) : [];
        $entity = static::_afterGet($entity);

        if (empty($entity) && $page_type) {
            $page_type_id = MenuCategoryItem::where('slug',$page_type)->first()->id;
            $this_ad_ids = DB::table('ad_category_relation')            
            ->where('menu_category_item_id', $page_type_id)
            ->pluck('ad_id');

            $entity = Ads::whereIn('ads_id', $this_ad_ids)
            ->where('status', 1)->inRandomOrder()->take(1)->get()->first();
            $entity = ($entity) ? $entity->toArray() : [];
            $entity = static::_afterGet($entity);
        }
        return $entity;
    }
    
    public static function saveItem($request, $preset = false, $langsData = []) {
        $data = !$preset ? $request->all() : $request;
        $validator = Validator::make($data, [
            'title' => 'required',
            'type' => 'required'
        ])->setAttributeNames([
            'title' => __('Ad Name'),
            'type' => __('Category')
        ]);
        if($validator->fails()) {
            return ['errors' => $validator->errors()->toArray()];
        }
        $id = (isset($data['ads_id']) && !empty($data['ads_id']) ? $data['ads_id'] : null);
        $new = is_null($id);
        $entity = static::findOrCreate($id);
        
        if(!$preset && !$new && !static::_canAdsEdit($entity)) {
            return redirect(url('/'));
        }
        
        if(empty($data['status'])) {
            $data['status'] = 1;
        }
        
        if (empty($data['country_id']) && !empty($data['iso3'])) {
            $data['country_id'] = Country::getCountryId($data['iso3'], 'iso3');
        }
        if (empty($data['country_id']) && !empty($data['country_name'])) {
            $countries = Country::searchCountries($data['country_name']);
            if (!empty($countries)) {
                $data['country_id'] = $countries[0]['id'];
            }
        }
        unset($data['iso3']);
        unset($data['country_name']);
        
        if(empty($data['country_id'])) {
            $data['country_id'] = null;
        }
        if(empty($data['city'])) {
            $data['city'] = null;
        }
        if(empty($data['state'])) {
            $data['state'] = null;
        }
        if(empty($data['address'])) {
            $data['address'] = null;
        }
        
        $keywords = !empty($data['keywords']) ? $data['keywords'] : '';
        $keywords = mb_strtolower($keywords);
        if (!empty($keywords)) {
            $keywords = explode(',', $keywords);
            $keywords = array_map('trim', $keywords);
            $keywords = array_unique($keywords);
            $keywords = array_filter($keywords, function($keyword) { return !is_null($keyword) && $keyword !== ''; });
            $data['keywords'] = implode(',', $keywords);
        }
        $data['file_link'] = isset($data['fileNew']) ? '' : $data['file_link'];
        
        $entity->fill($data);
        $entity->save();
        
        $entityData = $entity->toArray();

        $result = DB::table('ad_category_relation')->where('ad_id',$entityData['ads_id'])->first();
        $relations = [];
        $menu_categories = explode(',',$data['type']);
        $first_type = '';
        for ($i=0; $i < count($menu_categories); $i++) { 
            if ($i == 0) {
                $first_type = $menu_categories[$i];
            }
            $menu_category_id = MenuCategoryItem::where('slug',$menu_categories[$i])->first()->id;
            $relations[] = ['ad_id' => $entityData['ads_id'], 'menu_category_item_id' => $menu_category_id];
        }
        if ($result) {
            DB::table('ad_category_relation')->where('ad_id',$entityData['ads_id'])->delete();
            DB::table('ad_category_relation')->insert($relations);
        }
        else{
            DB::table('ad_category_relation')->insert($relations);
        }
        Ads::find($entityData['ads_id'])->update(['type' => $first_type]);
        
        if (!empty($keywords)) {
            static::deleteAdsKeywords($entityData['ads_id']);
            foreach ($keywords as $key => $keyword) {
                $keywords[$key] = [
                    'name' => $keyword,
                    'ads_id' => $entityData['ads_id'],
                    'key_hash' => md5($keyword)
                ];
            }
            DB::table('ads_keywords')->insert($keywords);
        }
        
        if (!empty($data['fileNew'])) {
            $data = Upload::attachUploads($data, $request, ['fileNew'], false);
            Upload::saveUploadedImages(isset($data['fileNew']) ? $data['fileNew'] : [], $entityData['ads_id'], static::$tableName);
            
            $data = [];
            $entityData['file_link'] = Upload::getUploadedImages($entityData['ads_id'], static::$tableName);
            $entityData['file_link'] = $data['file_link'] = '/uploads/'. trim($entityData['file_link'][0]['name']);
            $entity->fill($data);
            $entity->save();
        } elseif (empty($data['file_link'])) {
            Upload::deleteUploadedEntityImages($entityData['ads_id'], static::$tableName);
        }
        
        return $entityData;
    }
    
    static function deleteAdsKeywords($ads_id)
    {
        DB::table('ads_keywords')->where('ads_id', $ads_id)->delete();
    }
    
    public static function deleteAdsStatusById($id)
    {
        $ad = static::find($id);
        if(!static::_canAdsEdit($ad, false)) {
            return redirect(url('/'));
        }
        $user = Auth::user();
        if ($user->isAdmin()) {
            $ad->delete();
        } else {
            static::setStatus($id, 5);
        }
        return true;
    }
    
    public static function deleteAdsById($id)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $ad = static::find($id);
            $ad->delete();
            
            Upload::deleteUploadedEntityImages($id, static::$tableName);
            static::deleteAdsKeywords($id);
            DB::table('ad_category_relation')->where('ad_id', $id)->delete();
        }        
        return true;
    }
    
    public static function setStatus($id, $status)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $ad = static::find($id);
            $ad->fill(['status' => $status])->save();
            return $ad->toArray();
        }
        return true;
    }
    
    public static function getAds()
    {
        $entities = static::query();
        $entities = $entities->where('status', 1);
        $entities = $entities->orderBy('order', 'asc')->limit(10)->get()->toArray();
        return $entities;
    }
    
    public static function _afterGet($entity, $role = null, $relation = null)
    {
        $entityData = $entity;
        if (isset($entityData['ads_id'])) {
            $entityData['country_name'] = empty($entityData['country_id']) ? '' : Country::getCountryName($entityData['country_id']);
            $entityData['file_link'] = preg_replace('/\s+/','',$entityData['file_link']);
            $entityData['file'] = Upload::getUploadedImages($entityData['ads_id'], static::$tableName);
            $entityData['file_type'] = !empty($entityData['file']) ? $entityData['file'][0]['type'] : 0;
            $entityData['file'] = !empty($entityData['file']) ? '/uploads/' . trim($entityData['file'][0]['name']) : '';
            //dd($entityData);
            if (empty($entityData['file_type']) && !empty($entityData['file_link']) && !empty($entityData['file_link'])) {
                $entityData['file_type'] = Upload::getUploadTypeByUrl($entityData['file_link']);
            }
        }
        return $entityData;
    }

    public static function _getAdsFieldsList() {
        $entities = [];
        $menu_categories = MenuCategoryItem::all();       
        foreach ($menu_categories as $item) {
            $entities[] = ['id' => $item->slug, 'label' => __($item->label)];
        }
        $fields = [
            'relation' => [
                'type' => [
                    'index' => 'type',
                    'type' => 'multiselectbox',
                    'label' => __('Category'),
                    'value' => ['type'],
                    'options' => $entities
                ],
            ],
        ];
        
        return $fields;
    }

    public static function _getAdsTypes($id){
        $menu_category_item_ids = DB::table('ad_category_relation')            
            ->where('ad_id', $id)
            ->pluck('menu_category_item_id');
        $menu_category_items = MenuCategoryItem::whereIn('id',$menu_category_item_ids)->pluck('slug');            
        return $menu_category_items;
    }
}
