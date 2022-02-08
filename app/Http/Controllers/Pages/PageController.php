<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Page;
use CustomLaravelLocalization;
use BaseModel;
use Quote;
use Response;

class PageController extends Controller
{

	public function __construct()
	{
		$this->middleware('admin')->except(['getPageContent', 'getQuotesPage', 'searchQuotes']);

	}

	public function getPageContent(Request $request)
	{
		$data = $this->_presetData();
		$route = request()->route()->getName();

		if($route == 'page.contact') {

		} else {
			$name = substr($route, 5);
			$data['page_title'] = __(Page::getTitle($name));
			$data['page_content'] = Page::getContent($name);
		}
		//dd(request()->route()->getName(), $data);
		return $this->showData($data);
	}

	public function getAllPages(Request $request)
	{
		$data = $this->_presetData([
			'pages' => Page::getAll()
		]);
		return $this->showData($data);
	}

	public function editPage(Request $request, $name, $langId = null)
	{
		if(!$langId) {
			$langId = BaseModel::getDefaultLang();
		}
		$data = $this->_presetData([
			'page_name' => $name,
			'page_title' => Page::getTitle($name),
			'page_lang' => $langId,
			'page_content' => Page::getContent($name, $langId),
			'translations' => Page::getPageTranslations($name),
			'languages' => CustomLaravelLocalization::getSupportedLocales()
		]);

		//dd($data);
		return $this->showData($data);
	}

	public function savePage(Request $request)
	{
		$page = Page::saveContent($request);
		if($page) {
			return redirect(route('admin.edit.page', array('name' => $page->name, 'lang' => $page->lang_id)));
		}

		return redirect()->back();
	}

	public function editHome(Request $request)
	{
		$data = $this->_presetData([
			'homepage' => Page::getHomeAll(),
			'domains' => BaseModel::getDomainsList(false)
		]);
		//dd($data);
		return $this->showData($data);
	}

	public function saveHome(Request $request)
	{
		Page::saveHome($request);
		return redirect(route('admin.edit.home'));
	}

	public function editFooter(Request $request)
	{
		$data = $this->_presetData([
			'footer' => Page::getFooterAll(),
			'domains' => BaseModel::getDomainsList(false)
		]);
		//dd($data);
		return $this->showData($data);
	}

	public function saveFooter(Request $request)
	{
		Page::saveFooter($request);
		return redirect(route('admin.edit.footer'));
	}

	public function getQuotesPage(Request $request) {
	 	$data = $this->_presetData();
	 	return $this->showData($data);
	 }

	public function searchQuotes(Request $request) {
	 	$keyword = $request->get('keyword');
	
	 	if(isset($keyword)) {
	 		$data = Quote::searchQuotes($keyword);
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

	public function getAllQuotes(Request $request)
	{
	 	$params = $request->except('_token');
	 	$data = $this->_presetData([
	 		'quotes' => Quote::getQuotes($params),
	 		'filter' => $params,
	 	]);
	 	//dd($data);
	 	return $this->showData($data);
	 }

	public function addQuote(Request $request)
	{
	 	$phrase = $request->post('phrase');
	 	if(isset($phrase) && !empty($phrase)) {
	 		Quote::addQuote($phrase);
	 	}
	 	return redirect(route('admin.profile.quotes'));
	}
	public function deleteQuote(Request $request, $id)
	{
	 	Quote::deleteQuote($id);
	 	return redirect()->back();
	}

}
