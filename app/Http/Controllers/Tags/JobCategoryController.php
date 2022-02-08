<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JobCategory;

class JobCategoryController extends Controller
{
	public $model;
	public $tableKey = 'job_category_id';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function getAllJobCategories(Request $request, $slug = null)
	{
		$model = $this->getModel();
		$data = $this->_presetData([
			'jobCategories' => $model::getAll('job_category_id', ['slug' => $slug]),
		]);
		return $this->showData($data);
	}

	public function jobCategoryEditAdmin(Request $request, $param = null)
	{
		$model = $this->getModel();

		$jobCategory = $model::getEntity([$this->tableKey => $param]);

		$data = $this->_presetData([
			'jobCategory' => $jobCategory,
			'fields' => $model::getFields($jobCategory, $param),
		]);
		//dd($data);
		return $this->showData($data);
	}

	public function jobCategoryDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
		$model::where($this->tableKey, $param)->delete();
		return redirect(route('user.profile.jobCategories'));
	}

	public function jobCategorySave(Request $request)
	{
		$model = $this->getModel();
		return $model::saveItem($request);
	}
}
