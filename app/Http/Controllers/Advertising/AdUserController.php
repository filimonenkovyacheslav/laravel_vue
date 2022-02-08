<?php

namespace App\Http\Controllers\Advertising;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use AdUser;
use User;
use Country;

class AdUserController extends Controller
{

	public function __construct()
	{
		$this->middleware('admin');
	}

	public function getAdUsers(Request $request)
	{
		$params = static::getParamsFromRequest($request);
		$data = $this->_presetData([
			'ad_users' => AdUser::getAdUsers($params),
			'ad_user_roles' => User::getRelativeData(),
			'filter' => $params
		]);
		//dd($data);
		return $this->showData($data);
	}

	public function editAdUser(Request $request, $id)
	{
		$data = $this->_presetData([
			'ad_user' => AdUser::getAdUser($id),
			'ad_user_roles' => User::getRelativeData(),
		]);

		foreach(Country::all() as $i => $country) {
			$data['countries'][$country->id] = $country->name;
			$data['countries_codes'][$country->iso2] = $country->id;
		}

		//dd($data);
		return $this->showData($data);
	}

	public function saveAdUser(Request $request)
	{
		//dd($request);
		$adUser = AdUser::saveAdUser($request);
		if($adUser && $adUser->id) {
			return redirect(route('admin.edit.ad_user', array('id' => $adUser->id)));
		}

		return redirect()->back();
	}

	public function deleteAdUser(Request $request, $id)
	{
		AdUser::deleteAdUser($id);

		return redirect()->back();
	}

}
