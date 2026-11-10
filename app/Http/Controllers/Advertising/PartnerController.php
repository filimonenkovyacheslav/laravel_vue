<?php

namespace App\Http\Controllers\Advertising;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use BaseModel;
use Partner;
use PartnerDomain;

class PartnerController extends Controller
{

	public function __construct()
	{
		$this->middleware('admin');
	}

	public function getAllPartners(Request $request)
	{
		$data = $this->_presetData([
			'partners' => Partner::getAll()
		]);
		//dd($data);
		return $this->showData($data);
	}

	public function editPartner(Request $request, $id)
	{
		$data = $this->_presetData([
			'partner' => Partner::getPartner($id),
			'partner_domains' => PartnerDomain::getAllForPartner($id),
			'view_statuses' => Partner::getViewStatuses(),
			'domains' => BaseModel::getDomainsList(false),
		]);

		//dd($data);
		return $this->showData($data);
	}

	public function savePartner(Request $request)
	{
		//dd($request);
		$partner = Partner::savePartner($request);
		if($partner && $partner->id) {
			return redirect(route('admin.edit.partner', array('id' => $partner->id)));
		}

		return redirect()->back();
	}

	public function deletePartner(Request $request, $id)
	{
		Partner::deletePartner($id);

		return redirect()->back();
	}

}
