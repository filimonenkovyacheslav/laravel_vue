<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use WineCategory;

class WineCategoryController extends Controller
{
	public $model;
	public $tableKey = 'wine_category_id';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
    
    public function setCategoryStatus(Request $request, $id, $status)
    {
        $model = $this->getModel();
        $category = $model::setCategoryStatus($id, $status);
        
        if($category === false) {
            return redirect(route('user.profile.wineCategories'));
        }
    
        //$model::setCategoryWinesStatus($id, $status);
        
        return redirect()->back();
    }

	public function categoryEditAdmin(Request $request, $param = null)
	{
		$model = $this->getModel();

		$wineCategory = $model::getEntity([$this->tableKey => $param]);

		$data = $this->_presetData([
			'wineCategory' => $wineCategory,
			'fields' => $model::getFields($wineCategory, $param),
			'wine_categories_admin' => WineCategory::getAllList(true),
		]);
		
		return $this->showData($data);
	}

	public function categoryDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
		$model::deleteWineCategoriesById($param);
        /*$model::beforeDeleteCategory($param);
		$model::where($this->tableKey, $param)->delete();*/
		return redirect(route('user.profile.wineCategories'));
	}

	public function categorySave(Request $request, $back = null)
	{
		$model = $this->getModel();
		if ($back) {
            return $model::saveItem($request, [], false);
        } else {
            return $model::saveItem($request);
        }
	}
	
	public function bulkEditCategories(Request $request) {
        $entityList = $request->get('editItems');
        $entityList = explode(',',$entityList);
        $action = $request->get('action');
        if (!empty($entityList)) {
            switch ($action) {
	        	case 'publish':
	        		foreach($entityList as $entity) {
	                	static::setCategoryStatus($request, $entity, 1);
	            	}
	        		break;
	        	case 'unpublish':
	        		foreach($entityList as $entity) {
	                	static::setCategoryStatus($request, $entity, 0);
	            	}
	        		break;
	        	case 'delete':
	        		$model = $this->getModel();
		            foreach($entityList as $entity) {
        		        $model::deleteWineCategoriesById($entity);
            		}
	        		break;
	        	
	        	default:
	        		break;
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
    
    public function _getWineCategoryFields(Request $request)
    {
        $model = $this->getModel();
        
        return Response::json(['categories' => $model::_getWineFieldsList()], 200);
    }
}
