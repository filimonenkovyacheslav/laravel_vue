<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use ProductCategory;

class ProductCategoryController extends Controller
{
	public $model;
	public $tableKey = 'product_category_id';

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
            return redirect(route('user.profile.productCategories'));
        }
    
        //$model::setCategoryProductsStatus($id, $status);
        
        return redirect()->back();
    }

	public function categoryEditAdmin(Request $request, $param = null)
	{
		$model = $this->getModel();

		$productCategory = $model::getEntity([$this->tableKey => $param]);

		$data = $this->_presetData([
			'productCategory' => $productCategory,
			'fields' => $model::getFields($productCategory, $param),
			'product_categories_admin' => ProductCategory::getAllList(true),
		]);
		
		return $this->showData($data);
	}

	public function categoryDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
		$model::deleteProductCategoriesById($param);
        /*$model::beforeDeleteCategory($param);
		$model::where($this->tableKey, $param)->delete();*/
		return redirect(route('user.profile.productCategories'));
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
        		        $model::deleteProductCategoriesById($entity);
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
    
    public function _getProductCategoryFields(Request $request)
    {
        $model = $this->getModel();
        
        return Response::json(['categories' => $model::_getProductFieldsList()], 200);
    }
}
