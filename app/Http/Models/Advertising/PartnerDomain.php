<?php

namespace App\Http\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use BaseModel;
use Upload;

class PartnerDomain extends Model
{
	protected $table = 'partner_domains';
	
	public $fillable = [
		'partner_id', 'domain', 'title', 'name', 'logo', 'url'
	];

	public static function getAllForPartner($id) {
		$results = static::where('partner_id', $id)->orderBy('domain')->get();

		$results = $results ? $results->toArray() : [];
		$domains = [];
		foreach($results as $domain) 
		{
			$domains[] = static::_afterGet($domain);
		}

		return $domains;
	}

	public static function _afterGet($domain) {
		$domain['image'] = isset($domain['logo']) && !is_null($domain['logo']) ? Upload::getUploadById($domain['logo']) : [];
	
		return $domain;
	}
}