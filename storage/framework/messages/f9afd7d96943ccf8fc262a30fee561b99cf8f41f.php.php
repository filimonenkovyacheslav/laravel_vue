<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgencyController extends Controller
{
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['getAllAgencies', 'agencyView']]);
	}

	public function getAllAgencies(Request $request)
	{
		$name = $request->route()->getName();
        $data = [
            'name' => $name,
            'entity_type' => 'agency',
            'entities' => \App\Agency::allSorted('id'),
            'urls' => $this->_getUrls(['view']),
        ];
    	$params = json_encode(collect($data));
    	return view('index', compact('name', 'params'));
	}

	public function agencyView(Request $request, $id)
	{
		$name = $request->route()->getName();
		$agency = DB::table('agencies')->find($id);
		$data = [
            'name' => $name,
            'entity_type' => 'agency',
            'entity' => $agency,
            'urls' => $this->_getUrls(['list']),
        ];
        $params = json_encode(collect($data));
		return view('index', compact('name', 'params'));
	}

	public function getAllAgenciesAdmin(Request $request)
	{
		$name = $request->route()->getName();
        $data = [
            'name' => $name,
            'entity_type' => 'agency',
            'entities' => \App\Agency::allSorted('id'),
            'urls' => $this->_getUrls(['admin', 'view_admin', 'edit_admin', 'delete_admin']),
        ];
    	$params = json_encode(collect($data));
    	return view('index', compact('name', 'params'));
	}

	public function agencyViewAdmin(Request $request, $id)
	{
		$name = $request->route()->getName();
		$agency = DB::table('agencies')->find($id);
		$data = [
            'name' => $name,
            'entity_type' => 'agency',
            'entity' => $agency,
            'urls' => $this->_getUrls(['list_admin', 'edit_admin', 'delete_admin']),
        ];
		$params = json_encode(collect($data));
		return view('index', compact('name', 'params'));
	}

	public function agencyEditAdmin(Request $request, $id = null)
	{
		$name = $request->route()->getName();
		$agency = DB::table('agencies')->find($id);
		$data = [
            'name' => $name,
            'entity_type' => 'agency',
            'entity' => $agency,
            'fields' => $this->_getPropertyFields($agency),
            'urls' => $this->_getUrls(['view_admin', 'list_admin']),
            'errors' => !empty($errors) ? $errors : null,
        ];
        $params = json_encode(collect($data));
		return view('index', compact('name', 'params'));
	}

	public function agencyDeleteAdmin($id)
	{
		DB::table('agencies')->delete($id);
		return redirect(route('agency_list_admin'));
	}

	protected function _getPropertyFields($agency) {
		$fields = $this->_getPropertyFieldsList();

		if(!empty($agency)) {
			foreach($agency as $k => $v) {
				if(isset($fields[$k])) {
					$fields[$k]['value'] = $v;
				}
			}
		}
		return $fields;
	}

	protected function _getUrls($types) {
		$urls = [];
		foreach ($types as $t) {
			switch($t) {
				case 'admin':
					$urls['admin'] = '/admin';
					break;

				case 'list':
					$urls['entities_list'] = '/agencies';
					break;
				case 'view':
					$urls['entity_view'] = '/agency/view/';
					break;
				case 'edit':
					$urls['aentity_edit'] = '/agency/edit/';
					break;
				case 'delete':
					$urls['entity_delete'] = '/agency/delete/';
					break;

				case 'list_admin':
					$urls['entities_list'] = '/admin/agencies';
					break;
				case 'view_admin':
					$urls['entity_view'] = '/admin/agency/view/';
					break;
				case 'edit_admin':
					$urls['entity_edit'] = '/admin/agency/edit/';
					break;
				case 'delete_admin':
					$urls['entity_delete'] = '/admin/agency/delete/';
					break;
			}
		}
		return $urls;
	}

	protected function _getPropertyFieldsList() {
		return array(
			'id' => array(
				'type' => 'hidden',
				'label' => '',
				'value' => '',
			),
			'title' => array(
				'type' => 'text',
				'label' => 'Title',
				'value' => '',
			),
			'description' => array(
				'type' => 'textarea',
				'label' => 'Description',
				'value' => '',
			),
		);
	}
}
