<?php

namespace App\Http\Controllers\Tags;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use SimpleKeyword;

class SimpleKeywordController extends Controller
{
	public $model;
	public $tableKey = 'keyword_id';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['searchKeywords']]);
	}

	public function keywordSave(Request $request)
	{
		return SimpleKeyword::saveKeyword($request);
	}

	public function searchKeywords(Request $request) {
		$keyword = $request->get('keyword');
		$type = $request->get('type');
		if(isset($keyword)) {
			$data = SimpleKeyword::searchKeywords($type, $keyword);
			return Response::json([
				'results' => $data,
				'message' => 'Done'
			], 200);
		} else {
			return Response::json([
				'results' => [],
				'message' => 'Error'
			], 200);
		}
	}
}