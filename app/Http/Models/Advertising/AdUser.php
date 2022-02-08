<?php

namespace App\Http\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Validator;
use BaseModel;
use AdUserProfession;
use Upload;
use Role;
use Country;
use DB;

class AdUser extends Model
{
	public $fillable = [
		'name', 'media', 'url', 'role_id', 'map_address', 'lat', 'lng', 'country', 'state', 'city', 'street', 'house'
	];

	public static function getAdUserForView($role, $profession, $params) {
		$roleId = Role::where('name', $role)->value('id');
		if(!$roleId) return [];

		$filter = [		
			'role_id' => $roleId,
			'professions' => null,
			'country' => null,
			'state' => null,
			'city' => null,
			'street' => null,
			'address' => null
		];

		if($role == 'professional') {
			$filter['professions'] = [];
			if($profession) {
				$filter['professions'][] = $profession;
			} else if(isset($params['search_profession']) && !empty($params['search_profession'])) {
				$filter['professions'] = explode(',', $params['search_profession']);
			}
		}
		$geo = false;

		if(isset($params['ai']) && !empty($params['ai'])) {
			$country = Country::where('iso2', $params['ai'])->value('id');
			if($country) {
				$filter['country'] = $country;
			}
			$geo = true;
		}
		if(isset($params['as']) && !empty($params['as'])) {
			$filter['state'] = $params['as'];
			$geo = true;
		}
		if(isset($params['ac']) && !empty($params['ac'])) {
			$filter['city'] = $params['ac'];
			$geo = true;
		}
		if(isset($params['ar']) && !empty($params['ar'])) {
			$filter['street'] = $params['ar'];
			$geo = true;
		}

		if(!$geo && isset($params['search_location']) && !empty($params['search_location'])) {
			$filter['address'] = $params['search_location'];
		}

		$ad = static::getAdUserByParams($filter);
		if($ad) return $ad;
		$old = $filter;

		if(!is_null($filter['professions']) && sizeof($filter['professions']) > 0) {
			$filter['professions'] = [];
			$ad = static::getAdUserByParams($filter);
			if($ad) return $ad;
		}

		$filter = $old;
		if($geo) {
			$filter['street'] = null;
			$filter['city'] = null;
			$filter['state'] = null;
			if(!is_null($filter['country'])) {
				$filter['country'] = null;
				$ad = static::getAdUserByParams($filter);
				if($ad) return $ad;
			}
		}

		if(!is_null($filter['address'])) {
			$filter['address'] = null;
			$ad = static::getAdUserByParams($filter);
			if($ad) return $ad;
		}

		if(!is_null($filter['professions']) && sizeof($filter['professions']) > 0) {
			$filter['professions'] = [];
			$ad = static::getAdUserByParams($filter);
			if($ad) return $ad;
		}

		return [];
	}

	public static function getAdUserByParams($params) {
		$ad = static::where('media', '!=', null);
				
		if(!is_null($params['professions'])) {
			if(sizeof($params['professions']) == 0) {
				$ad->leftJoin('ad_users_professions as p', 'p.ad_user_id', '=', 'ad_users.id')->whereNull('p.id');
			} else {
				$ad->join('ad_users_professions as p', 'p.ad_user_id', '=', 'ad_users.id')->whereIn('p.profession_id', $params['professions']);
			}
		}

		$ad->where('role_id', $params['role_id']);
		if(is_null($params['address'])) {
			$orders = [];
			$ad->where('country', $params['country']);

			if(is_null($params['state'])) {
				$ad->whereNull('state');
			} else {
				$ad->where(function($query) use($params) {
					$query->where('state', $params['state'])
						->orWhere('state',null);
				});
				$orders[] = 'state';
			} 
			if(is_null($params['city'])) {
				$ad->whereNull('city');
			} else {
				$ad->where(function($query) use($params) {
					$query->where('city', $params['city'])
						->orWhere('city',null);
				});
				$orders[] = 'city';
			}
			if(is_null($params['street'])) {
				$ad->whereNull('street');
			} else {
				$ad->where(function($query) use($params) {
					$query->where('street', $params['street'])
						->orWhere('street',null);
				});
				$orders[] = 'street';
			}
			foreach ($orders as $field) {
				$ad->orderBy($field);
			}
		} else {
			$ad->where('map_address', 'ilike', '%'.$params['address'].'%');
		}
		//dd($ad->toSql());
		$ad = $ad->inRandomOrder()->first();
		if($ad) {
			$ad = $ad->toArray();
			$ad['image'] = Upload::getUploadById($ad['media']);
		}
		return $ad;
	}

	public static function getAdUsers($params) {
		$ads = static::orderBy('id');

		if(isset($params['role']) && !empty($params['role'])) {
			$roleId = Role::where('name', $params['role'])->value('id');
			if($roleId) {
				$ads->where('role_id', $roleId);
			}
		}
		if(isset($params['name']) && !empty($params['name'])) {
			$ads->where('name', 'ilike', '%'.$params['name'].'%');
		}

		$pagination = $ads->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($ad) {
			return static::_afterAllGet($ad);
		});
		return $pagination;
	}

	public static function getAdUser($id) {
		$ad = static::find($id);

		return static::_afterGet($ad ? $ad->toArray() : []);
	}

	public static function formatCharField($val, $maxLen = 100, $canNull = true, $def = '') {
		if(!$canNull && is_null($val)) {
			$val = $def;
		}
		if(!is_null($val) && $maxLen > 0) {
			$val = substr($val, 0, $maxLen);
		}

		return $val;
	}

	public static function saveAdUser($request) {
		$data = $request->except('_token');
		$data['name'] = static::formatCharField((isset($data['name']) ? $data['name'] : null), 150, false);
		$data['url'] = static::formatCharField((isset($data['url']) ? $data['url'] : null), 255, false);
		$data['map_address'] = static::formatCharField((isset($data['map_address']) ? $data['map_address'] : null), 255);
		$data['lat'] = static::formatCharField((isset($data['lat']) ? $data['lat'] : null));
		$data['lng'] = static::formatCharField((isset($data['lng']) ? $data['lng'] : null));
		$data['state'] = static::formatCharField((isset($data['state']) ? $data['state'] : null));
		$data['city'] = static::formatCharField((isset($data['city']) ? $data['city'] : null));
		$data['street'] = static::formatCharField((isset($data['street']) ? $data['street'] : null));
		$data['house'] = static::formatCharField((isset($data['house']) ? $data['house'] : null));
		$data['lat'] = static::formatCharField((isset($data['lat']) ? $data['lat'] : null));

		if(!is_null($data['url']) && !empty($data['url']) && substr($data['url'], 0, 4) != 'http') {
			$data['url'] = 'http://'.$data['url'];
		}

		$id = (isset($data['id']) ? $data['id'] : null);

		$ad = static::find($id);
		if(!$ad) {
			$ad = new static;
		}
		$data['role_id'] = isset($data['role_name']) && !empty($data['role_name']) ? Role::where('name', $data['role_name'])->value('id') : 0;
		$data = Upload::attachUploads($data, $request, ['media_id'], false);
		$ad->fill($data)->save();

		AdUserProfession::saveProfessions($ad->id, $data['role_name'] == 'professional' && isset($data['professions']) ? $data['professions'] : '');

		return $ad;
	}

	public static function deleteAdUser($id) {
		$ad = static::find($id);
		if(!$ad) return;
		AdUserProfession::where('ad_user_id', $id)->delete();
		if(!is_null($ad->media)) {
			Upload::deleteUpload(null, $ad->media);
		}
		static::where('id', $id)->delete();
	}

	public static function _afterAllGet($ad) {
		if(!$ad || empty($ad)) return null;
		$ad['role_name'] = $ad['role_id'] ? Role::where('id', $ad['role_id'])->value('name') : '';

		return $ad;
	}

	public static function _afterGet($ad) {
		if(!$ad) {
			$ad = ['id' => 0];
		}

		$ad['image'] = isset($ad['media']) && !is_null($ad['media']) ? Upload::getUploadById($ad['media']) : [];
		$ad['role_name'] = isset($ad['role_id']) && !empty($ad['role_id']) ? Role::where('id', $ad['role_id'])->value('name') : 'agency';
		$ad['professions'] = AdUserProfession::getProfessionsList($ad['id']);
		
		return $ad;
	}
}