<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use PropertyCategory;

class PropertyCategoryController extends Controller
{
	public $model;
	public $tableKey = 'property_category_id';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function categoryEditAdmin(Request $request, $param = null)
	{
		$model = $this->getModel();

		$category = $model::getEntity([$this->tableKey => $param]);

		$data = $this->_presetData([
			'propertyCategory' => $category,
			'fields' => $model::getFields($category, $param),
			'property_categories_admin' => PropertyCategory::getAllList(true),
		]);
		
		return $this->showData($data);
	}

	public function categoryDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
        $model::beforeDeleteCategory($param);
		$model::where($this->tableKey, $param)->delete();
		return redirect(route('user.profile.propertyCategories'));
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
    
    public function _getPropertyCategoryFields(Request $request)
    {
        $model = $this->getModel();
        
        return Response::json(['categories' => $model::_getPropertyFieldsList(false)], 200);
    }
}
