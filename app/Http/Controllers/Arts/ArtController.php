<?php

namespace App\Http\Controllers\Arts;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use Art;
use UploadArt;
use Email;
use User;
use Auth;
use Country;
use CustomLaravelLocalization;
use Ads;
use ArtFavorite;
use ArtCategory;

class ArtController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'id';
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllArts', 'getArtBySlug', 'getAllArtsJson', 'getArtBySlugJson', 'searchLocations']]);
    }
    
    public function getAllArts(Request $request)
    {
        $params = static::getParamsFromRequest($request);
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        //$category = request('category', 0);
        $category = empty($params['category']) ? 0 : $params['category'];

        $data = $this->_presetData([
            'entity_type' => 'art',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
            'selected_parents' => empty($category) ? [] : array_reverse(ArtCategory::getSelectedCategoryParents($category)),
            'art_categories_filter' => ArtCategory::getCategoriesHierarchy($category, '', false, true, false, true),
            'art_categories' => ArtCategory::getAllListParent(),
            'art_categories_front' => ArtCategory::getAllList(),
        ]);

        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->id] = $country->iso3;
        }
        
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', $params, ['search_type' => 'art'], 1);
        }
        
        return $this->showData($data);
    }
    
    public function getArtBySlug(Request $request, $param)
    {
        $model = $this->getModel();
        $art = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($art['id']) || $art['status'] == 5) return redirect('404');
        
        $data = $this->_presetData([
            'entity' => $art,
            'entities_similar' => $model::getAll([
                'country' => $art['country'],
                'city' => $art['city'],
                'not_in' => [$art['id']],
            ], ['user']),
            'art_categories_front' => ArtCategory::getAllList(),
        ]);
        
        return $this->showData($data);
    }
    
    public function editArt(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        $art = $model::_addTranslation($model::where('arts.id', $param))->first();
        if(!is_null($param)) {
            if(!isset($art['author']) || ($user->id != $art['author'] && !$user->isAdmin())) {
                return redirect(url('/'));
            }
        }
        if(!in_array($user->role()->first()->name, ['artist', 'gallery', 'administrator'])) {
            return redirect(url('/'));
        }
        $data = $this->_presetData([
            'id' => $param
        ]);
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->iso2] = $country->id;
            $data['countries_names'][$country->name] = $country->id;
        }
        return $this->showData($data);
    }
    
    public function _getArt(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('id', $param);
        
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteArt(Request $request, $id)
    {
        $model = $this->getModel();
        $model::deleteArtById($id);
        return redirect()->back();
    }
    
    public function unpublishArt(Request $request, $id)
    {
        return $this->setArtStatus($request, $id, 6);
    }
    
    public function bulkEditArts(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $statusId = $request->get('status');
        $model = $this->getModel();
        if ( !empty($entityList) && isset($statusId) ) {
            foreach($entityList as $entity) {
                static::setArtStatus($request, $entity, $statusId);
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

     public function bulkLabelArts(Request $request) {
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
                $model::setArtLabel($entity, $label);
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
    
    public function bulkDeleteArts(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $model = $this->getModel();
        if ( !empty($entityList) ) {
            foreach($entityList as $entity) {
                $model::deleteArtById($entity);
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
    
    public function saveArt(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            if(is_null($request->id) || empty($request->id)) {
                $attributes = [
                    'entity_url' => url(route('art.view.frontend', ['slug' => $result['slug']])),
                    'entity_title' => $result['title']
                ];
                Email::send('new_art', $attributes);
                return Response::json(['message' => __('New Art was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('art.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
            }
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function setArtStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $art = $model::setArtStatus($id, $status);
        
        if($art === false) {
            return redirect(route('user.profile.arts'));
        }
        
        if(isset($art['status']) && $art['status'] == 1) {
            $attributes = [
                'entity_url' => url(route('art.view.frontend', ['slug' => $art['slug']])),
                'entity_title' => $art['title'],
                'edit_url' => url(route('art.edit.admin', ['id' => $art['id']])),
            ];
            $user = User::findOrFail($art['author']);
            foreach($user->toArray() as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['user_' . $key] = $value;
                }
            }
            Email::send('approve_art', $attributes);
        }
        return redirect()->back();
    }

    public function setArtLabel(Request $request, $id, $label)
    {
        $model = $this->getModel();
        $model::setArtLabel($id, $label);
        
        return redirect()->back();
    }
    
    public function toggleFavoriteArt(Request $request)
    {
        $favorite = ArtFavorite::toggleFavorite($request->except('_token'));
        
        return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
    }
    
    public function getImagesModel() {
        if(empty($this->imagesModel)) {
            $this->imagesModel = UploadArt::class;
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
            $locations = Art::searchArtForLocations($keyword, 10);
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
    
    public function getAllArtsJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'art',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getArtBySlugJson(Request $request, $param) {
        $model = $this->getModel();
        $art = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($art['id']) || $art['status'] == 5) {
            return Response::json([
                'entity' => null
            ], 404);
        }
        
        $data = $this->_presetData([
            'entity' => $art,
            'entities_similar' => $model::getAll([
                'country' => $art['country'],
                'city' => $art['city'],
                'not_in' => [$art['id']],
            ], ['user']),
        ]);
        
        return Response::json($data, 200);
    }
}
