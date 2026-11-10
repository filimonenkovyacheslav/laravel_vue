<?php

namespace App\Http\Controllers\News;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use News;
use NewsFavorite;
use UploadNews;
use Response;
use Email;
use User;
use Auth;
use Country;
use CustomLaravelLocalization;
use Ads;

class NewsController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'id';
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllNews', 'getNewsBySlug', 'getAllNewsJson', 'getNewsBySlugJson', 'searchLocations']]);
    }
    
    public function getAllNews(Request $request)
    {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $params = static::getParamsFromRequest($request);
        $data = $this->_presetData([
            'entity_type' => 'news',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order'])
        ]);
    
        $data['entity_arr'] = $data['entities']->items();
        
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->id] = $country->iso3;
        }
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', static::getParamsFromRequest($request), ['search_type' => 'news'], 1);
        }
        //dd($data, $category);
        //dd(config('app.env'),config('app.debug'));
        return $this->showData($data);
    }
    
    public function getNewsBySlug(Request $request, $param)
    {
        $model = $this->getModel();
        $item = $model::getByParam('slug', $param, ['user']);
        if(!isset($item['id']) || $item['status'] == 5) return redirect('404');
        
        $data = $this->_presetData([
            'entity' => $item
        ]);
        return $this->showData($data);
    }
    
    public function editNews(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        $item = $model::_addTranslation($model::where('news.id', $param))->first();
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
    
    public function _getNews(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('id', $param);
        
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteNews(Request $request, $id)
    {
        $model = $this->getModel();
        $model::deleteNewsById($id);
        return redirect()->back();
    }
    
    public function unpublishNews(Request $request, $id)
    {
        return $this->setNewsStatus($request, $id, 6);
    }  

    public function saveNewsUploadItem(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveUploadItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'redirect' => route('news.edit.admin', ['id' => $result['id']]), 'entity' => News::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }  
    
    public function saveNews(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            if(is_null($request->id) || empty($request->id)) {
                $attributes = [
                    'entity_url' => url(route('news.view.frontend', ['slug' => $result['slug']])),
                    'entity_title' => $result['title']
                ];
                Email::send('new_news', $attributes);
                return Response::json(['message' => __('New Article was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('news.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
            }
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function setNewsStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $entity = $model::setNewsStatus($id, $status);
        
        if($entity === false) {
            return redirect(route('user.profile.news'));
        }
        
        if(isset($entity['status']) && $entity['status'] == 1) {
            $attributes = [
                'entity_url' => url(route('news.view.frontend', ['slug' => $entity['slug']])),
                'entity_title' => $entity['title'],
                'edit_url' => url(route('news.edit.admin', ['id' => $entity['id']])),
            ];
            $user = User::findOrFail($entity['author']);
            foreach($user->toArray() as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['user_' . $key] = $value;
                }
            }
            Email::send('approve_news', $attributes);
        }
        return redirect()->back();
    }
    
    public function setNewsLabel(Request $request, $id, $label)
    {
        $model = $this->getModel();
        $model::setNewsLabel($id, $label);
        
        return redirect()->back();
    }
    
    public function toggleFavorite(Request $request)
    {
        $favorite = NewsFavorite::toggleFavorite($request->except('_token'));
        
        return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
    }
    
    public function getImagesModel() {
        if(empty($this->imagesModel)) {
            $this->imagesModel = UploadNews::class;
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
            $locations = News::searchNewsForLocations($keyword, 10);
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
    
    public function getAllNewsJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'news',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getNewsBySlugJson(Request $request, $param) {
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

    public function bulkEditNews(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $statusId = $request->get('status');
        
        if ( !empty($entityList) && isset($statusId) ) {
            foreach($entityList as $entity) {
                static::setNewsStatus($request, $entity, $statusId);
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
    
    public function bulkLabelNews(Request $request) {
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
                $model::setNewsLabel($entity, $label);
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
    
    public function bulkDeleteNews(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $model = $this->getModel();
        if ( !empty($entityList) ) {
            foreach($entityList as $entity) {
                $model::deleteNewsById($entity);
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
}
