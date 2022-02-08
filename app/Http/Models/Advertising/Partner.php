<?php

namespace App\Http\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use BaseModel;
use PartnerDomain;
use Upload;
use DB;

class Partner extends Model
{
	public $fillable = [
		'title', 'name', 'logo', 'url', 'view_all'
	];

	public static function getViewStatuses() {
		return [
			1 => __('All Domains'),
			0 => __('Only Selected')
		];
	}

	public static function getAllForDomain($domain) {
		$results = static::select(DB::raw('partners.id, COALESCE(d.title, partners.title) as title, COALESCE(d.name, partners.name) as name, COALESCE(d.logo, partners.logo) as logo, COALESCE(d.url, partners.url) as url'))
			->leftJoin('partner_domains as d', function ($join) use($domain){
           		$join->on('d.partner_id', '=', 'partners.id')
                	->where('d.domain', $domain);})
			->where('view_all', 1)
			->orWhere('d.id', '!=', null)
			->orderBy('partners.id')->get();

		$results = $results ? $results->toArray() : [];
		$partners = [];
		foreach($results as $partner) 
		{
			$partners[] = static::_afterGet($partner);
		}

		return $partners;
	}

	public static function getAll() {
		$pagination = static::orderBy('id')->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($partner) {
			return static::_afterAllGet($partner);
		});
		return $pagination;
	}

	public static function getPartner($id) {
		$partner = static::find($id);

		return static::_afterGet($partner ? $partner->toArray() : []);
	}

	public static function savePartner($request) {
		$data = $request->except('_token');

		if(!isset($data['title'])) {
			$data['title'] = 'Partner';
		}
		if(!isset($data['name'])) {
			$data['name'] = '';
		}
		if(!isset($data['url'])) {
			$data['url'] = '';
		}
		$id = (isset($data['id']) ? $data['id'] : null);

		$partner = static::find($id);
		if(!$partner) {
			$partner = new static;
		}
		$data = Upload::attachUploads($data, $request, ['logo_id'], false);
		$partner->fill($data)->save();

		if($partner) {
			$id = $partner->id;
			PartnerDomain::where('partner_id', $id)->delete();

			$domains = [];
			foreach($data as $name => $value) {
				if(strpos($name, 'domain_') === 0) {
					$endLocale = strpos($name, '_', 7);
					if($endLocale > 0) {
						$domain = substr($name, 7, $endLocale - 7);
						$domains[$domain][substr($name, $endLocale + 1)] = $value;
					}
				}
			}
			foreach($domains as $domain => $data) {
				$data = Upload::attachUploads($data, $request, ['domain_'.$domain.'_logo_id'], false);

				if(isset($data['domain_'.$domain.'_logo'])) {
					$data['logo'] = $data['domain_'.$domain.'_logo'];
				} else if(isset($data['domain_logo'])) {
					$data['logo'] = $data['domain_logo'];
				}
				$data['partner_id'] = $id;
				$data['domain'] = $domain;
				PartnerDomain::create($data)->save();
			}
		}
		//dd($domains);

		return $partner;
	}

	public static function deletePartner($id) {
		PartnerDomain::where('partner_id', $id)->delete();
		Partner::where('id', $id)->delete();
	}

	public static function _afterAllGet($partner) {
		if(!$partner || empty($partner)) return null;
		$partner['domains'] = PartnerDomain::where('partner_id', $partner->id)->count();

		return $partner;
	}

	public static function _afterGet($partner) {
		$partner['image'] = isset($partner['logo']) && !is_null($partner['logo']) ? Upload::getUploadById($partner['logo']) : [];

		if(!isset($partner['view_all'])) {
			$partner['view_all'] = 1;
		}
		
		return $partner;
	}
}