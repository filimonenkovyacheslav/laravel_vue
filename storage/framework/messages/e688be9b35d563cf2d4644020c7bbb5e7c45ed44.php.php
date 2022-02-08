<?php

namespace App\Http\Controllers\Properties;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['getAllProperties', 'getPropertyBySlug']]);
	}

	public function getAllProperties(Request $request)
	{
		$name = $request->route()->getName();
        $data = [
            'name' => $name,
            'entity_type' => 'property',
            'entities' => \App\Property::allSorted('id'),
            'urls' => $this->_getUrls(['view']),
        ];
    	$params = json_encode(collect($data));
    	return view('index', compact('name', 'params'));
	}

	public function getPropertyBySlug(Request $request, $id)
	{
		$name = $request->route()->getName();
		$property = DB::table('properties')->find($id);
		$data = [
            'name' => $name,
            'entity_type' => 'property',
            'entity' => $property,
            'urls' => $this->_getUrls(['list']),
        ];
        $params = json_encode(collect($data));
		return view('index', compact('name', 'params'));
	}

	public function getAllPropertiesAdmin(Request $request)
	{
		$name = $request->route()->getName();
        $data = [
            'name' => $name,
            'entity_type' => 'property',
            'entities' => \App\Property::allSorted('id'),
            'urls' => $this->_getUrls(['admin', 'view_admin', 'edit_admin', 'delete_admin']),
        ];
    	$params = json_encode(collect($data));
    	return view('index', compact('name', 'params'));
	}

	public function getPropertyBySlugAdmin(Request $request, $id)
	{
		$name = $request->route()->getName();
		$property = DB::table('properties')->find($id);
		$data = [
            'name' => $name,
            'entity_type' => 'property',
            'entity' => $property,
            'urls' => $this->_getUrls(['list_admin', 'edit_admin', 'delete_admin']),
        ];
		$params = json_encode(collect($data));
		return view('index', compact('name', 'params'));
	}

	public function editProperty(Request $request, $id = null)
	{
		$name = $request->route()->getName();
		$property = DB::table('properties')->find($id);
		$data = [
            'name' => $name,
            'entity_type' => 'property',
            'entity' => $property,
            'fields' => $this->_getPropertyFields($property),
            'urls' => $this->_getUrls(['view_admin', 'list_admin']),
            'errors' => !empty($errors) ? $errors : null,
        ];
        $params = json_encode(collect($data));
		return view('index', compact('name', 'params'));
	}

	public function deleteProperty($id)
	{
		DB::table('properties')->delete($id);
		return redirect(route('property.list.admin'));
	}

	protected function _getPropertyFields($property) {
		$fields = $this->_getPropertyFieldsList();

		if(!empty($property)) {
			foreach($property as $k => $v) {
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
					$urls['entities_list'] = '/properties';
					break;
				case 'view':
					$urls['entity_view'] = '/property/view/';
					break;
				case 'edit':
					$urls['entity_edit'] = '/property/edit/';
					break;
				case 'delete':
					$urls['entity_delete'] = '/property/delete/';
					break;

				case 'list_admin':
					$urls['entities_list'] = '/admin/properties';
					break;
				case 'view_admin':
					$urls['entity_view'] = '/admin/property/view/';
					break;
				case 'edit_admin':
					$urls['entity_edit'] = '/admin/property/edit/';
					break;
				case 'delete_admin':
					$urls['entity_delete'] = '/admin/property/delete/';
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
			'details' => array(
				'type' => 'textarea',
				'label' => 'Details',
				'value' => '',
			),
			'features' => array(
				'type' => 'textarea',
				'label' => 'Features',
				'value' => '',
			),
			'address' => array(
				'type' => 'text',
				'label' => 'Address',
				'value' => '',
			),
			'map' => array(
				'type' => 'text',
				'label' => 'Map',
				'value' => '',
			),
			'lat' => array(
				'type' => 'hidden',
				'label' => '',
				'value' => '',
			),
			'lng' => array(
				'type' => 'hidden',
				'label' => '',
				'value' => '',
			),
			'photos' => array(
				'type' => 'file',
				'label' => 'Photo',
				'value' => '',
			),
			'video' => array(
				'type' => 'text',
				'label' => 'Video',
				'value' => '',
			),
			'share' => array(
				'type' => 'text',
				'label' => 'Share',
				'value' => '',
			),
			'contact' => array(
				'type' => 'text',
				'label' => 'Contact',
				'value' => '',
			),
		);
	}
}