<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Feature;

class FeatureController extends Controller
{
	public $model;
	public $tableKey = 'feature_id';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/*public function getAllFeatures(Request $request)
	{
		$model = $this->getModel();
		$data = $this->_presetData([
			'features' => $model::getAll('feature_id'),
		]);
		return $this->showData($data);
	}*/

	public function featureEditAdmin(Request $request, $param = null)
	{
		$model = $this->getModel();
		$feature = $model::getEntity([$this->tableKey => $param]);
		$data = $this->_presetData([
			'feature' => $feature,
			'fields' => $model::getFields($feature, $param),
		]);
		return $this->showData($data);
	}

	public function featureDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
		$model::where($this->tableKey, $param)->delete();
		return redirect(route('user.profile.features'));
	}

	public function featureSave(Request $request)
	{
		$model = $this->getModel();
		return $model::saveItem($request);
	}
}
