<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use ArtCategory;

class ArtCategoryController extends Controller
{
	public $model;
	public $tableKey = 'art_category_id';

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
			'artCategory' => $category,
			'fields' => $model::getFields($category, $param),
			'art_categories_admin' => ArtCategory::getAllList(true),
		]);
		
		return $this->showData($data);
	}

	public function categoryDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
        $model::beforeDeleteCategory($param);
		$model::where($this->tableKey, $param)->delete();
		return redirect(route('user.profile.artCategories'));
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
    
    public function _getArtCategoryFields(Request $request)
    {
        $model = $this->getModel();
        
        return Response::json(['categories' => $model::_getArtFieldsList()], 200);
    }
}
