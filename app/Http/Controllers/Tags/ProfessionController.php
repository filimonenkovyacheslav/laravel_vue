<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Profession;
use Upload;
use Response;

class ProfessionController extends Controller
{
	public $model;
	public $tableKey = 'profession_id';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/*public function getAllProfessions(Request $request, $slug = null)
	{
		$model = $this->getModel();
		$data = $this->_presetData([
			'professions' => $model::getAll('profession_id', ['slug' => $slug]),
		]);
		return $this->showData($data);
	}*/

	public function professionEditAdmin(Request $request, $param = null)
	{
		$model = $this->getModel();
		$profession = $model::getEntity([$this->tableKey => $param]);

		$profession['imgLogoLink'] = !empty($profession['img_logo']) ? Upload::getUploadById($profession['img_logo']) : [];
		$profession['imgBackgroundLink'] = !empty($profession['img_background']) ? Upload::getUploadById($profession['img_background']) : [];

		$profession['imgLogoLink'] = !empty($profession['imgLogoLink']['name']) ? '/uploads/'.$profession['imgLogoLink']['name'] :  '/images/logo-profilepic.jpg';
		$profession['imgBackgroundLink'] = !empty($profession['imgBackgroundLink']['name']) ? '/uploads/'.$profession['imgBackgroundLink']['name'] :  '/images/logo-profilepic.jpg';

		$data = $this->_presetData([
			'profession' => $profession,
			'fields' => $model::getFields($profession, $param),
		]);
		//dd($data);
		return $this->showData($data);
	}

	public function professionDeleteAdmin(Request $request, $param)
	{
		$model = $this->getModel();
		$model::where($this->tableKey, $param)->delete();
		return redirect(route('user.profile.professions'));
	}

	public function professionSave(Request $request)
	{
		$model = $this->getModel();
		$model::prepareSaveItem($request);
		return Response::json([
			'users' => [],
			'route' => route('user.profile.professions'),
			'message' => 'Done'
		], 200);
	}
}
