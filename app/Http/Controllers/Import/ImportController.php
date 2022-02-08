<?php

namespace App\Http\Controllers\Import;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ImportJob;
use ImportLink;
use ImportRun;
use ImportLog;
use Auth;
use User;

class ImportController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function getImportLinks(Request $request)
	{
		$params = $request->except('_token');

		$data = $this->_presetData([
			'import_links' => ImportLink::getImportLinks($params),
			'import_statuses' => ImportLink::getStatuses(),
			'filter' => $params,
			]);
		if(!Auth::user()->isAdmin()) {
			unset($data['import_statuses'][3]);
		}
		//dd($data);
		return $this->showData($data);
	}

	public function getImportRuns(Request $request, $id = null)
	{
		$links = ImportLink::getAllImportLinks();
		$found = false;
		if($id) {
			foreach($links as $link) {
				if($link['id'] == $id) {
					$found = $link;
					break;
				}
			}
		}
		if(!$found && sizeof($links) > 0) {
			$found = $links[0];
		}
		if($found) {
			$id = $found['id'];
			$found = ImportLink::getImportLinkById($id);
		} else {
			$id = 0;
		}

		$data = $this->_presetData([
			'import_links' => $links,
			'import_runs' => ImportRun::getRunsByLink($id),
			'import_statuses' => ImportLink::getStatuses(),
			'run_statuses' => ImportRun::getStatuses(),
			'filter' => ['link' => $found],
			]);
		//dd($data);
		return $this->showData($data);
	}

	public function getImportLog(Request $request)
	{
		$params = $request->except('_token');
		$id = isset($params['id']) && is_numeric($params['id']) ? $params['id'] : 0;
		$run = ImportRun::find($id);
		$user = Auth::user();
		if(!$run) {
			return redirect(route('home'));
		}

		$run = ImportRun::_afterGet($run);
		$link = ImportLink::getImportLinkById($run['link_id']);
		if(!$link || (!$user->isAdmin() && $link['author'] != $user->id)) {
			return redirect(route('home'));
		}
		$typeLog = isset($params['type']) ? $params['type'] : null;

		$data = $this->_presetData([
			'import_log' => ImportLog::getLogByRunId($id, $typeLog),
			'filter' => ['link' => $link, 'run' => $run, 'type' => $typeLog],
			]);
		//dd($data);
		return $this->showData($data);
	}

	public function addImportLink(Request $request)
	{
		$link = $request->post('link');
		if(isset($link) && !empty($link)) {
			ImportLink::addImportLink($link);
		}
		return redirect(route('user.import.links'));
	}

	public function setImportLinkStatus(Request $request, $id, $status)
	{
		$model = $this->getModel();
		if($id > 0) {
			ImportLink::setImportLinkStatus($id, $status);
		}
		return redirect()->back();
	}

	public function runImport(Request $request, $id = null)
	{
		//ImportRun::doExport();
		ImportRun::runImport($id);

		/*if(ImportRun::runImport($id)) {
			dispatch(new ImportJob())->onQueue('import');
			//ImportRun::doImport();
		}*/
		return redirect()->back();
		//return redirect(route('user.import.links'));
	}
}
