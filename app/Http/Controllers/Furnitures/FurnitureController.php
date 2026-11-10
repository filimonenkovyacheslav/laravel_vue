<?php

namespace App\Http\Controllers\Furnitures;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Furniture;
use PropertyPrice;
use FurnitureFavorite;
use UploadFurniture;
use Response;
use Email;
use User;
use Auth;
use Furnitureseller;
use Country;
use CustomLaravelLocalization;
use Measure;
use Ads;
use FurnitureCategory;

class FurnitureController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'id';
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllFurnitures', 'getFurnitureBySlug', 'getAllFurnituresJson', 'getFurnitureBySlugJson', 'searchLocations']]);
    }
    
    public function getAllFurnitures(Request $request)
    {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $params = static::getParamsFromRequest($request);
        $category = empty($params['category']) ? 0 : $params['category']; //request('category', 0);
        $data = $this->_presetData([
            'entity_type' => 'furniture',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
            'selected_parents' => empty($category) ? [] : array_reverse(FurnitureCategory::getSelectedCategoryParents($category)),
            'furniture_categories_filter' => FurnitureCategory::getCategoriesHierarchy($category, '', false, true, false, true),
            //'furniture_categories' => FurnitureCategory::getAllListParent(),
            'furniture_categories_front' => FurnitureCategory::getAllList(),
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->id] = $country->iso3;
        }
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', static::getParamsFromRequest($request), ['search_type' => 'furniture'], 1);
        }
        //dd($data, $category);
        //dd(config('app.env'),config('app.debug'));
        return $this->showData($data);
    }
    
    public function getFurnitureBySlug(Request $request, $param)
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
            'furniture_categories_front' => FurnitureCategory::getAllList(),
        ]);
       
        return $this->showData($data);
    }
    
    public function editFurniture(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        $item = $model::_addTranslation($model::where('furnitures.id', $param))->first();
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
    
    public function _getFurniture(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('id', $param);
        
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteFurniture(Request $request, $id)
    {
        $model = $this->getModel();
        $model::deleteFurnitureById($id);
        return redirect()->back();
    }

    public function cloneFurniture(Request $request, $param = null)
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
            'route_name' => 'furniture.edit.admin',
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->iso2] = $country->id;
            $data['countries_names'][$country->name] = $country->id;
        }
        return $this->showData($data);
    }
    
    public function unpublishFurniture(Request $request, $id)
    {
        return $this->setFurnitureStatus($request, $id, 6);
    }
    
    public function bulkEditFurnitures(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $statusId = $request->get('status');
        
        if ( !empty($entityList) && isset($statusId) ) {
            foreach($entityList as $entity) {
                static::setFurnitureStatus($request, $entity, $statusId);
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
    
    public function bulkLabelFurnitures(Request $request) {
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
                $model::setFurnitureLabel($entity, $label);
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
    
    public function bulkDeleteFurnitures(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $model = $this->getModel();
        if ( !empty($entityList) ) {
            foreach($entityList as $entity) {
                $model::deleteFurnitureById($entity);
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
    
    public function saveFurniture(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            if(is_null($request->id) || empty($request->id)) {
                $attributes = [
                    'entity_url' => url(route('furniture.view.frontend', ['slug' => $result['slug']])),
                    'entity_title' => $result['title']
                ];
                Email::send('new_furniture', $attributes);
                return Response::json(['message' => __('New Furniture was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('furniture.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
            }
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function setFurnitureStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $entity = $model::setFurnitureStatus($id, $status);
        
        if($entity === false) {
            return redirect(route('user.profile.furnitures'));
        }
        
        if(isset($entity['status']) && $entity['status'] == 1) {
            $attributes = [
                'entity_url' => url(route('furniture.view.frontend', ['slug' => $entity['slug']])),
                'entity_title' => $entity['title'],
                'edit_url' => url(route('furniture.edit.admin', ['id' => $entity['id']])),
            ];
            $user = User::findOrFail($entity['author']);
            foreach($user->toArray() as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['user_' . $key] = $value;
                }
            }
            Email::send('approve_furniture', $attributes);
        }
        return redirect()->back();
    }
    
    public function setFurnitureLabel(Request $request, $id, $label)
    {
        $model = $this->getModel();
        $model::setFurnitureLabel($id, $label);
        
        return redirect()->back();
    }
    
    public function toggleFavoriteFurniture(Request $request)
    {
        $favorite = FurnitureFavorite::toggleFavoriteFurniture($request->except('_token'));
        
        return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
    }
    
    public function getImagesModel() {
        if(empty($this->imagesModel)) {
            $this->imagesModel = UploadFurniture::class;
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
            $locations = Furniture::searchFurnitureForLocations($keyword, 10);
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
    
    public function getAllFurnituresJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'furniture',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getFurnitureBySlugJson(Request $request, $param) {
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
