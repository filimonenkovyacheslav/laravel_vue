<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Good;
use PropertyPrice;
use GoodFavorite;
use UploadGood;
use Response;
use Email;
use User;
use Auth;
use Country;
use CustomLaravelLocalization;
use Measure;
use Ads;
use GoodCategory;

class GoodController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'id';
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllGoods', 'getGoodBySlug', 'getAllGoodsJson', 'getGoodBySlugJson', 'searchLocations']]);
    }
    
    public function getAllGoods(Request $request)
    {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $params = static::getParamsFromRequest($request);
        $category = empty($params['category']) ? 0 : $params['category']; //request('category', 0);
        $data = $this->_presetData([
            'entity_type' => 'good',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
            'selected_parents' => empty($category) ? [] : array_reverse(GoodCategory::getSelectedCategoryParents($category)),
            'good_categories_filter' => GoodCategory::getCategoriesHierarchy($category, '', false, true, false, true),
            'good_categories' => GoodCategory::getAllListParent(),
            'good_categories_front' => GoodCategory::getAllList(),
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->id] = $country->iso3;
        }
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', static::getParamsFromRequest($request), ['search_type' => 'good'], 1);
        }
        //dd($data, $category);
        return $this->showData($data);
    }
    
    public function getGoodBySlug(Request $request, $param)
    {
        $model = $this->getModel();
        $item = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($item['id']) || $item['status'] == 5) return redirect('404');
        
        $data = $this->_presetData([
            'entity' => $item,
            'entities_similar' => $model::getAll([
                'country' => $item['country'],
                'city' => $item['city'],
                'not_in' => [$item['id']],
            ], ['user']),
            'good_categories_front' => GoodCategory::getAllList(),
        ]);
       //dd($data);
        return $this->showData($data);
    }
    
    public function editGood(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        $item = $model::_addTranslation($model::where('goods.id', $param))->first();
        if(!is_null($param)) {
            if(!isset($item['author']) || ($user->id != $item['author'] && !$user->isAdmin())) {
                return redirect(url('/'));
            }
        }
        
        $data = $this->_presetData([
            'id' => $param,
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->iso2] = $country->id;
            $data['countries_names'][$country->name] = $country->id;
        }
        
        return $this->showData($data);
    }
    
    public function _getGood(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('id', $param);
        
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteGood(Request $request, $id)
    {
        $model = $this->getModel();
        $model::deleteGoodById($id);
        return redirect()->back();
    }

    public function cloneGood(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        if(!is_null($param)) {
            $item = $model::getByParam('id', $param);
            //$entity = $model::_addTranslation($model::where('designs.id', $param))->first();
            if(!isset($item['author']) || ($user->id != $item['author'] && !$user->isAdmin())) {
                return redirect(url('/'));
            }
        } else {
            return redirect(url('/'));
        }
        $id = $model::cloneItem($item);
        $data = $this->_presetData([
            'id' => $id,
            'route_name' => 'good.edit.admin',
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->iso2] = $country->id;
            $data['countries_names'][$country->name] = $country->id;
        }
        return $this->showData($data);
    }
    
    public function unpublishGood(Request $request, $id)
    {
        return $this->setGoodStatus($request, $id, 6);
    }
    
    public function bulkEditGoods(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $statusId = $request->get('status');
        
        if ( !empty($entityList) && isset($statusId) ) {
            foreach($entityList as $entity) {
                static::setGoodStatus($request, $entity, $statusId);
            }
            return Response::json([
                'users' => [],
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'users' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    
    public function bulkLabelGoods(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',', $entityList);;
        $label = $request->get('label');
        $label = !empty($label) ? $label : '';
        if ( !empty($label) && ($label === 'remove') ) {
            $label = 0;
        }
        $model = $this->getModel();
        if ( !empty($entityList) && isset($label) ) {
            foreach($entityList as $entity) {
                $model::setGoodLabel($entity, $label);
            }
            return Response::json([
                'users' => [],
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'users' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    
    public function bulkDeleteGoods(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $model = $this->getModel();
        if ( !empty($entityList) ) {
            foreach($entityList as $entity) {
                $model::deleteGoodById($entity);
            }
            return Response::json([
                'users' => [],
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'users' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    
    public function saveGood(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            if(is_null($request->id) || empty($request->id)) {
                $attributes = [
                    'entity_url' => url(route('good.view.frontend', ['slug' => $result['slug']])),
                    'entity_title' => $result['title']
                ];
                Email::send('new_good', $attributes);
                return Response::json(['message' => __('New Good was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('good.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
            }
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function setGoodStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $entity = $model::setGoodStatus($id, $status);
        
        if($entity === false) {
            return redirect(route('user.profile.goods'));
        }
        
        if(isset($entity['status']) && $entity['status'] == 1) {
            $attributes = [
                'entity_url' => url(route('good.view.frontend', ['slug' => $entity['slug']])),
                'entity_title' => $entity['title'],
                'edit_url' => url(route('good.edit.admin', ['id' => $entity['id']])),
            ];
            $user = User::findOrFail($entity['author']);
            foreach($user->toArray() as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['user_' . $key] = $value;
                }
            }
            Email::send('approve_good', $attributes);
        }
        return redirect()->back();
    }
    
    public function setGoodLabel(Request $request, $id, $label)
    {
        $model = $this->getModel();
        $model::setGoodLabel($id, $label);
        
        return redirect()->back();
    }
    
    public function toggleFavorite(Request $request)
    {
        $favorite = GoodFavorite::toggleFavorite($request->except('_token'));
        
        return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
    }
    
    public function getImagesModel() {
        if(empty($this->imagesModel)) {
            $this->imagesModel = UploadGood::class;
        }
        return $this->imagesModel;
    }
    
    public function searchCountries(Request $request) {
        $keyword = $request->get('keyword');
        
        if(isset($keyword)) {
            $data = Country::searchCountries($keyword);
            return Response::json([
                'data' => $data,
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'data' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    
    public function searchLocations(Request $request) {
        $keyword = $request->get('keyword');
        
        if(isset($keyword)) {
            $locations = Good::searchGoodForLocations($keyword, 10);
            $countries = Country::searchCountriesAsLocations($keyword, 5);
            $locations = array_merge($locations, $countries);
            return Response::json([
                'data' => $locations,
                'message' => 'Done'
            ], 200);
        } else {
            return Response::json([
                'data' => [],
                'message' => 'Error'
            ], 200);
        }
    }
    
    public function getAllGoodsJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'good',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getGoodBySlugJson(Request $request, $param) {
        $model = $this->getModel();
        $entity = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($entity['id']) || $entity['status'] == 5) {
            return Response::json([
                'entity' => null
            ], 404);
        }
        
        $data = $this->_presetData([
            'entity' => $entity,
            'entities_similar' => $model::getAll([
                'country' => $entity['country'],
                'city' => $entity['city'],
                'not_in' => [$entity['id']],
            ], ['user']),
        ]);
        
        return Response::json($data, 200);
    }
}
