<?php

namespace App\Http\Controllers\Designs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use Design;
use UploadDesign;
use Email;
use User;
use Auth;
use Country;
use CustomLaravelLocalization;
use Ads;
use DesignFavorite;
use DesignCategory;

class DesignController extends Controller
{
    public $model;
    public $imagesModel;
    public $tableKey = 'id';
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getAllDesigns', 'getDesignBySlug', 'getAllDesignsJson', 'getDesignBySlugJson', 'searchLocations']]);
    }
    
    public function getAllDesigns(Request $request)
    {
        $params = static::getParamsFromRequest($request);
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        //$category = request('category', 0);
        $category = empty($params['category']) ? 0 : $params['category'];

        $data = $this->_presetData([
            'entity_type' => 'design',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
            'selected_parents' => empty($category) ? [] : array_reverse(DesignCategory::getSelectedCategoryParents($category)),
            'design_categories_filter' => DesignCategory::getCategoriesHierarchy($category, '', false, true, false, true),
            'design_categories' => DesignCategory::getAllListParent(),
            'design_categories_front' => DesignCategory::getAllList(),
        ]);

        /*$data = $this->_presetData([
            'entity_type' => 'design',
            'entities' => $model::getAll($params, ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);*/
    
        foreach(Country::all() as $i => $country) {
            $data['countries'][$country->id] = $country->name;
            $data['countries_codes'][$country->id] = $country->iso3;
        }
        
        if ($data['entities']->total()) {
            $data['ads'] = Ads::getByParam('all', $params, ['search_type' => 'design'], 1);
        }
        //dd($data);
        return $this->showData($data);
    }
    
    public function getDesignBySlug(Request $request, $param)
    {
        $model = $this->getModel();
        $project = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($project['id']) || $project['status'] == 5) return redirect('404');
        
        $data = $this->_presetData([
            'entity' => $project,
            'entities_similar' => $model::getAll([
                'country' => $project['country'],
                'city' => $project['city'],
                'not_in' => [$project['id']],
            ], ['user']),
            'design_categories_front' => DesignCategory::getAllList(),
        ]);
        
        return $this->showData($data);
    }
    
    public function editDesign(Request $request, $param = null)
    {
        $user = Auth::user();
        $model = $this->getModel();
        $project = $model::_addTranslation($model::where('designs.id', $param))->first();
        if(!is_null($param)) {
            if(!isset($project['author']) || ($user->id != $project['author'] && !$user->isAdmin())) {
                return redirect(url('/'));
            }
        }
        if(!in_array($user->role()->first()->name, ['architect_firm', 'building_company', 'design_company', 'administrator'])) {
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
        //dd($data);
        return $this->showData($data);
    }
    
    public function _getDesign(Request $request, $param = null)
    {
        $model = $this->getModel();
        $entity = $model::getByParam('id', $param);
        
        return Response::json(['entity' => $entity], 200);
    }
    
    public function deleteDesign(Request $request, $id)
    {
        $model = $this->getModel();
        $model::deleteDesignById($id);
        return redirect()->back();
    }
    
    public function unpublishDesign(Request $request, $id)
    {
        return $this->setDesignStatus($request, $id, 6);
    }
    
    public function bulkEditDesigns(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $statusId = $request->get('status');
        $model = $this->getModel();
        if ( !empty($entityList) && isset($statusId) ) {
            foreach($entityList as $entity) {
                static::setDesignStatus($request, $entity, $statusId);
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

    public function bulkLabelDesigns(Request $request) {
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
                $model::setDesignLabel($entity, $label);
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
    
    public function bulkDeleteDesigns(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $model = $this->getModel();
        if ( !empty($entityList) ) {
            foreach($entityList as $entity) {
                $model::deleteDesignById($entity);
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
    
    public function saveDesign(Request $request)
    {
        $model = $this->getModel();
        $result = $model::saveItem($request);
        if($result && is_array($result) && empty($result['errors'])) {
            if(is_null($request->id) || empty($request->id)) {
                $attributes = [
                    'entity_url' => url(route('design.view.frontend', ['slug' => $result['slug']])),
                    'entity_title' => $result['title']
                ];
                Email::send('new_project', $attributes);
                return Response::json(['message' => __('New Project was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('design.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
            }
            return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
        }
        return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
    }
    
    public function setDesignStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $project = $model::setDesignStatus($id, $status);
        
        if($project === false) {
            return redirect(route('user.profile.designs'));
        }
        
        if(isset($project['status']) && $project['status'] == 1) {
            $attributes = [
                'entity_url' => url(route('design.view.frontend', ['slug' => $project['slug']])),
                'entity_title' => $project['title'],
                'edit_url' => url(route('design.edit.admin', ['id' => $project['id']])),
            ];
            $user = User::findOrFail($project['author']);
            foreach($user->toArray() as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['user_' . $key] = $value;
                }
            }
            Email::send('approve_project', $attributes);
        }
        return redirect()->back();
    }

    public function setDesignLabel(Request $request, $id, $label)
    {
        $model = $this->getModel();
        $model::setDesignLabel($id, $label);
        
        return redirect()->back();
    }
    
    public function toggleFavoriteDesign(Request $request)
    {
        $favorite = DesignFavorite::toggleFavorite($request->except('_token'));
        
        return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
    }
    
    public function getImagesModel() {
        if(empty($this->imagesModel)) {
            $this->imagesModel = UploadDesign::class;
        }
        return $this->imagesModel;
    }
    
    public function getAllDesignsJson(Request $request) {
        $model = $this->getModel();
        $orderBy = $model::getSortOrder($request);
        $data = $this->_presetData([
            'entity_type' => 'design',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
        
        return Response::json($data, 200);
    }
    
    public function getDesignBySlugJson(Request $request, $param) {
        $model = $this->getModel();
        $project = $model::getByParam('slug', $param, ['user', 'country']);
        if(!isset($project['id']) || $project['status'] == 5) {
            return Response::json([
                'entity' => null
            ], 404);
        }
        
        $data = $this->_presetData([
            'entity' => $project,
            'entities_similar' => $model::getAll([
                'country' => $project['country'],
                'city' => $project['city'],
                'not_in' => [$project['id']],
            ], ['user']),
        ]);
        
        return Response::json($data, 200);
    }
}
