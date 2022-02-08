<?php

namespace App\Http\Controllers\Wines;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Wine;
use PropertyPrice;
use WineFavorite;
use UploadWine;
use Response;
use Email;
use User;
use Auth;
use Wineseller;
use Country;
use CustomLaravelLocalization;
use Measure;
use Ads;
use WineCategory;

class WineController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'id';
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllWines', 'getWineBySlug', 'getAllWinesJson', 'getWineBySlugJson', 'searchLocations']]);
    }
    
    public function getAllWines(Request $request)
    {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $params = static::getParamsFromRequest($request);
        $category = empty($params['category']) ? 0 : $params['category']; //request('category', 0);
        $data = $this->_presetData([
            'entity_type' => 'wine',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
            'selected_parents' => empty($category) ? [] : array_reverse(WineCategory::getSelectedCategoryParents($category)),
            'wine_categories_filter' => WineCategory::getCategoriesHierarchy($category, '', false, true, false, true),
            //'wine_categories' => WineCategory::getAllListParent(),
            'wine_categories_front' => WineCategory::getAllList(),
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->id] = $country->iso3;
        }
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', static::getParamsFromRequest($request), ['search_type' => 'wine'], 1);
        }
        //dd($data, $category);
        //dd(config('app.env'),config('app.debug'));
        return $this->showData($data);
    }
    
    public function getWineBySlug(Request $request, $param)
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
            'wine_categories_front' => WineCategory::getAllList(),
        ]);
       
        return $this->showData($data);
    }
    
    public function editWine(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        $item = $model::_addTranslation($model::where('wines.id', $param))->first();
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
    
    public function _getWine(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('id', $param);
        
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteWine(Request $request, $id)
    {
        $model = $this->getModel();
        $model::deleteWineById($id);
        return redirect()->back();
    }

    public function cloneWine(Request $request, $param = null)
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
            'route_name' => 'wine.edit.admin',
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->iso2] = $country->id;
            $data['countries_names'][$country->name] = $country->id;
        }
        return $this->showData($data);
    }
    
    public function unpublishWine(Request $request, $id)
    {
        return $this->setWineStatus($request, $id, 6);
    }
    
    public function bulkEditWines(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $statusId = $request->get('status');
        
        if ( !empty($entityList) && isset($statusId) ) {
            foreach($entityList as $entity) {
                static::setWineStatus($request, $entity, $statusId);
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
    
    public function bulkLabelWines(Request $request) {
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
                $model::setWineLabel($entity, $label);
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
    
    public function bulkDeleteWines(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $model = $this->getModel();
        if ( !empty($entityList) ) {
            foreach($entityList as $entity) {
                $model::deleteWineById($entity);
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
    
    public function saveWine(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            if(is_null($request->id) || empty($request->id)) {
                $attributes = [
                    'entity_url' => url(route('wine.view.frontend', ['slug' => $result['slug']])),
                    'entity_title' => $result['title']
                ];
                Email::send('new_wine', $attributes);
                return Response::json(['message' => __('New Wine was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('wine.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
            }
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function setWineStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $entity = $model::setWineStatus($id, $status);
        
        if($entity === false) {
            return redirect(route('user.profile.wines'));
        }
        
        if(isset($entity['status']) && $entity['status'] == 1) {
            $attributes = [
                'entity_url' => url(route('wine.view.frontend', ['slug' => $entity['slug']])),
                'entity_title' => $entity['title'],
                'edit_url' => url(route('wine.edit.admin', ['id' => $entity['id']])),
            ];
            $user = User::findOrFail($entity['author']);
            foreach($user->toArray() as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['user_' . $key] = $value;
                }
            }
            Email::send('approve_wine', $attributes);
        }
        return redirect()->back();
    }
    
    public function setWineLabel(Request $request, $id, $label)
    {
        $model = $this->getModel();
        $model::setWineLabel($id, $label);
        
        return redirect()->back();
    }
    
    public function toggleFavoriteWine(Request $request)
    {
        $favorite = WineFavorite::toggleFavoriteWine($request->except('_token'));
        
        return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
    }
    
    public function getImagesModel() {
        if(empty($this->imagesModel)) {
            $this->imagesModel = UploadWine::class;
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
            $locations = Wine::searchWineForLocations($keyword, 10);
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
    
    public function getAllWinesJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'wine',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getWineBySlugJson(Request $request, $param) {
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
