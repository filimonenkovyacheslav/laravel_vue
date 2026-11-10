<?php

namespace App\Http\Controllers\Franchises;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Franchise;
use UploadFranchise;
use Feature;
use Response;
use Email;
use User;
use Auth;
use Country;
use CustomLaravelLocalization;

class FranchiseController extends Controller
{
    public $model;
	public $imagesModel;
	public $tableKey = 'id';

    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['getAllFranchises', 'getFranchiseBySlug']]);
	}

	public function getAllFranchises(Request $request)
	{
		$model = $this->getModel();
		$orderBy = $model::getSortOrder($request);
		$data = $this->_presetData([
			'entity_type' => 'franchise',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);
		//dd($data);
		return $this->showData($data);
	}

	public function getFranchiseBySlug(Request $request, $param)
	{
		$model = $this->getModel();
		$franchise = $model::getByParam('slug', $param, ['user', 'country'], 1);
		if(!isset($franchise['id'])) abort(404);

		$data = $this->_presetData([
            'entity' => $franchise,
			'entities_similar' => $model::getAll([
				'country' => $franchise['country'],
				'city' => $franchise['city'],
				'not_in' => [$franchise['id']],
			], ['user']),
        ]);
		//dd($data);
		return $this->showData($data);
	}

	public function editFranchise(Request $request, $param = null)
	{
		$model = $this->getModel();
		$franchise = $model::_addTranslation($model::where('franchises.id', $param))->first();
		if(!is_null($param)) {
			$user = Auth::user();
			if(!isset($franchise['author']) || (($user->id != $franchise['author'] || !$user->isRole('franchise')) && !$user->isAdmin())) {
				return redirect(url('/'));
			}
		}
		$data = $this->_presetData([
            'id' => $param,
		]);
		foreach(Country::all() as $i => $country) {
			$data['countries'][$country->id] = $country->name;
			$data['countries_codes'][$country->iso2] = $country->id;
		}
		//dd($data);
		return $this->showData($data);
	}

	public function _getFranchise(Request $request, $param = null)
	{
		$model = $this->getModel();
		$entity = $model::getByParam('id', $param);
		//dd($entity);
		return Response::json(['entity' => $entity], 200);
	}

	public function deleteFranchise(Request $request, $id)
	{
		$model = $this->getModel();
		$model::deleteFranchiseById($id);
		return redirect()->back();
		//return $this->setFranchiseStatus($request, $id, 5);
	}

	public function unpublishFranchise(Request $request, $id)
	{
		return $this->setFranchiseStatus($request, $id, 6);
	}

	public function saveFranchise(Request $request)
	{
		$model = $this->getModel();
		$result = $model::saveItem($request);
		if($result && is_array($result) && empty($result['errors'])) {
			if(is_null($request->id) || empty($request->id)) {
				$attributes = [
					'entity_url' => url(route('franchise.view.frontend', ['slug' => $result['slug']])),
					'entity_title' => $result['title']
				];
				Email::send('new_franchise', $attributes);
				return Response::json(['message' => __('New Franchise was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('franchise.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
			}
			return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
		}
		return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
	}

	public function setFranchiseStatus(Request $request, $id, $status)
	{
		$model = $this->getModel();
		$franchise = $model::setFranchiseStatus($id, $status);

		// if($franchise === false) {
		// 	return redirect(route('user.profile.franchises'));
		// }

		if(isset($franchise['status']) && $franchise['status'] == 1) {
			$attributes = [
				'entity_url' => url(route('franchise.view.frontend', ['slug' => $franchise['slug']])),
				'entity_title' => $franchise['title'],
				'edit_url' => url(route('franchise.edit.admin', ['id' => $franchise['id']])),
			];
			$user = User::findOrFail($franchise['author']);
			foreach($user->toArray() as $key => $value) {
				if(!is_null($value) && !is_array($value)) {
					$attributes['user_' . $key] = $value;
				}
			}
			Email::send('approve_franchise', $attributes);
		}
		return redirect()->back();
	}

	public function setFranchiseLabel(Request $request, $id, $label)
	{
		$model = $this->getModel();
		$model::setFranchiseLabel($id, $label);

		return redirect()->back();
	}

	public function getImagesModel() {
		if(empty($this->imagesModel)) {
			$this->imagesModel = UploadFranchise::class;
		}
		return $this->imagesModel;
	}
}
