<?php

namespace App\Http\Controllers\Parsers;

use Illuminate\Http\Request;
use App\Jobs\ParserJob;
use App\Http\Controllers\Controller;
use Parser;
use Setting;
use BaseParser;

class ParserController extends Controller
{
	public $model;
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function getAllParsers(Request $request)
	{
		$data = $this->_presetData([
			'parsers' => Parser::getAll(),
			'parser_statuses' => Parser::getStatuses(),
			'proxies' => BaseParser::getProxiesStatus(),
		]);
		return $this->showData($data);
	}
	public function startParser(Request $request, $id)
	{
		$parser = Parser::setStartingStatus($id);
		if($parser) {
		//if(true) {
			Setting::setValue('parsers', strtolower($parser->model).'_proxies', '');
			dispatch(new ParserJob($id))->onQueue('parser'.$id);
			//BaseParser::doParse($id);
			//\Artisan::call('schedule:run');
		}
		return redirect(route('admin.profile.parsers'));
	}
	public function stopParser(Request $request, $id)
	{
		Parser::setStoppingStatus($id);
		return redirect()->back();
	}

	public function savePoxies(Request $request)
	{
		if(isset($request->delete_dead) && $request->delete_dead == 1) {
			BaseParser::deleteDeadProxies();
		} else {
			$list = $request->proxies;
			$proxies = $list ? array_map('trim', str_replace('OK', '', explode("\n", $list))) : [];
			BaseParser::saveProxies(array_values(array_unique($proxies)));
		}
		return redirect()->back();
	}
}
