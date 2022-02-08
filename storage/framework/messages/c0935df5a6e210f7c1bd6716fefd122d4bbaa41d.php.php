<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use stdClass;
// use Xinax\LaravelGettext\Facades\LaravelGettext;
// use Illuminate\Support\Facades\Redirect;
// use Illuminate\Support\Facades\Url;

class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index']]);
    }

    /**
     * Show the application home page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $name = $request->route()->getName();
        $data = [
            'name' => $name,
        ];
        $params = json_encode(collect($data));
        return view('index', compact('name', 'params'));
    }

	/**
	 * Show the application admin area.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function admin(Request $request)
	{
        $name = $request->route()->getName();
        $data = [
            'name' => $name,
            'urls' => [
                '/admin/properties' => 'All Properties',
                '/admin/agencies' => 'All Agencies',
            ],
        ];
        $params = json_encode(collect($data));
        return view('index', compact('name', 'params'));
    }
}
