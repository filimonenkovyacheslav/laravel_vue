<?php

namespace App\Http\Models\Franchises;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Auth;
use DB;
use CustomLaravelLocalization;
use Validator;
use User;
use Upload;
use SearchHelper;
use ElasticSearchHelper;
use Country;
use Role;

class Franchise extends \App\Http\Models\BaseModel
{
	use Sluggable;

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'title'
			]
		];
	}
	public $fillable = [
		'title', 'slug', 'author', 'status', 'label',
		'description', 'founded', 'fee', 'investment', 'terms',
		'address', 'country', 'state', 'city', 'map_address', 'lat', 'lng'
	];
	public static $type = 'franchise';
	public static $tableName = 'franchises';
	public static $langTable = 'franchise_langs';
	public static $langKey = 'franchise_id';
	public static $langFields = 'franchise_langs';

	public static $translatable = [
		'title', 'description', 'address',
	];

	public static function getFranchiseData($key = '') {
		$franchiseData = [
			'status' => [
				'publish' => ['id' => 1, 'label' => __('Published')],
				'pending' => ['id' => 2, 'label' => __('Pending')],
				'deleted' => ['id' => 5, 'label' => __('Deleted')],
				'unpublished' => ['id' => 6, 'label' => __('Unpublished')],
			],
			'label' => [
				'premium' => ['id' => 1, 'label' => __('Premium'), 'color' => 'blue'],
				'featured' => ['id' => 2, 'label' => __('Featured'), 'color' => 'orange'],
			],
		];

		return empty($key) ? $franchiseData : $franchiseData[$key];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'author');
	}

	public function country()
    {
      return $this->belongsTo(Country::class, 'country');
    }

	public static function _addTranslation($query, $keepSelect = false)
	{
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		if($langId == $defLang) return $query;

		$defTable = 'franchises';
		$langTable = 'franchise_langs';
		$fieldPrefix = 'lang_';
		$translatable = static::$translatable;

		$query = static::replaceQuery($query, $translatable, $defTable, $langTable, $fieldPrefix);

		if(!$keepSelect) {
			$query->select($defTable.'.*');
		}

		$query->leftJoin($langTable, function ($join) use($defTable, $langTable, $langId) {
				$join->on($langTable.'.franchise_id', '=', $defTable.'.id')
					->where($langTable.'.lang_id', '=', $langId);
			})
			->addSelect(DB::raw(static::getLangFieldsList($translatable, $defTable, $translatable, $langTable, $fieldPrefix)));

		return $query;
	}



	public static function _canFranchiseEdit($franchise, $adminOnly = false) {
		$user = Auth::user();
		$admin = $user->isAdmin();
		if($adminOnly && !$admin) return false;

		if(!$franchise || !isset($franchise->author) || (($user->id != $franchise->author || !$user->isRole('franchise')) && !$admin)) {
			return false;
		}
		return true;
	}

	public static function getAll($params = [], $with = [], $orderBy = 'id', $order = 'asc')
	{
		$defaultOrder = is_null($orderBy) || empty($orderBy);
		$orderBy = !empty($orderBy) ? $orderBy : 'id';
		$order = !empty($order) ? $order : 'desc';

		$entities = static::query();

		if(!empty($params)) {
			$entities = SearchHelper::applyCommonSearchParams($entities, $params);
		}
		$entities->where('status', 1)->with($with);
		if($defaultOrder) {
			$entities->orderBy('franchises.label', 'asc')->orderBy($orderBy, $order);
		} else {
			$entities->orderBy($orderBy, $order)->orderBy('franchises.label', 'asc');
		}
		$pagination = static::_addTranslation($entities)->paginate(static::$pagination);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity);
		});
		return $pagination;
	}

	public static function getByParam($param, $value, $with = ['user'], $status = null)
	{
		$entity = static::where('franchises.'.$param, $value);
		if(!is_null($status)) {
			$entity->where('status', $status);
		}

		$entity = static::_addTranslation($entity)->with($with)->first();
		$entity = static::_afterGet($entity);

		//$entity['user'] = isset($entity['user']) ? User::_afterGet($entity['user']) : null;

		return $entity;
	}

	public static function saveItem($request, $preset = false, $langsData = []) {
		if(!$preset && config('app')['localization_type'] == 1) {
			CustomLaravelLocalization::setLocaleLL($request->getSession()->get('locale'));
		}
		$data = !$preset ? $request->all() : $request;
		$validator = Validator::make($data, [
			'title' => 'required',
		])->setAttributeNames([
			'title' => __('Franchise Name'),
		]);
		if($validator->fails()) {
			return ['errors' => $validator->errors()->toArray()];
		}
		$id = (isset($data['id']) && !empty($data['id']) ? $data['id'] : null);
		$new = is_null($id);
		$entity = static::findOrCreate($id);

		if(!$preset && !$new && !static::_canFranchiseEdit($entity)) {
			return redirect(url('/'));
		}

		$data['slug'] = null;

		$defLang = static::getDefaultLang();
		$langId = $preset && isset($data['langId']) ? $data['langId'] : CustomLaravelLocalization::getLocaleCode();
		if(!$preset && $defLang != $langId) {
			$langData = [];
			foreach(static::$translatable as $field) {
				if(isset($data[$field])) {
					$langData[$field] = $data[$field];
					if(!$new) {
						unset($data[$field]);
					}
				}
			}
			if(sizeof($langData) > 0) {
				$langsData[$langId] = $langData;
			}
		}
		$entity->fill($data);
		$entity->save();
		$id = $entity->id;
		if(isset($langsData) && sizeof($langsData) > 0) {
			foreach($langsData as $langId => $langData) {
				$lang = FranchiseLang::where([['franchise_id', $id], ['lang_id', $langId]])->first();
				if(!$lang) {
					$langData['franchise_id'] = $id;
					$langData['lang_id'] = $langId;
					$lang = new FranchiseLang;
				}
				$lang->fill($langData)->save();
				ElasticSearchHelper::updateElasticEntity($lang, 'franchises');
			}
		} else {
			ElasticSearchHelper::updateElasticEntity($entity, 'franchises');
		}
		Upload::saveUploadedImages(isset($data['photos']) ? $data['photos'] : [], $id, static::$tableName);
		Upload::makeImageFeatured($id, static::$tableName, isset($data['featured_image']) ? $data['featured_image'] : null);

		$entity = $entity->toArray();

		if(!$preset && Auth::user()->isAdmin()) {
			$user = User::find($entity['author']);
			$entity['author_name'] = $user->first_name.' '.$user->last_name.' (ID '.$user->id.')';
		}

		return $entity;
	}

	public static function deleteFranchiseStatusById($id)
	{
		$franchise = static::find($id);
		if(!static::_canFranchiseEdit($franchise, false)) {
			return redirect(url('/'));
		}
		$user = Auth::user();
		if ($user->isAdmin()) {
			$franchise->delete();
		} else {
			static::setFranchiseStatus($id, 5);
		}
		return true;
	}

	public static function setFranchiseStatus($id, $status)
	{
		$franchise = static::find($id);
		if(!static::_canPropertyEdit($franchise, $status == 6)) {
			return redirect(url('/'));
		}
		if(!static::_canFranchiseEdit($franchise, $status == 1)) {
			return redirect(url('/'));
		}
		if($franchise->status != $status) {
			$franchise->fill(['status' => $status])->save();
			return $franchise->toArray();
		}
		return true;
	}

	public static function setFranchiseLabel($id, $label)
	{
		$franchise = static::find($id);

		if(!static::_canFranchiseEdit($franchise, true)) {
			return redirect(url('/'));
		}
		$labels = static::getFranchiseData('label');
		$labelId = isset($labels[$label]) ? $labels[$label]['id'] : null;

		if($franchise->label != $labelId) {
			$franchise->fill(['label' => $labelId])->save();
		}
		return true;
	}

	public static function _afterGet($entity, $role = null, $relation = null)
	{
		$entityData = $entity;

		if(!empty($entityData)) {
			$entityData = !is_array($entityData) ? $entityData->toArray() : $entityData;

			$id = $entityData['id'];
			$entityData['uploadsList'] = Upload::getUploadedImages($id, static::$tableName);
			$gallery = false;
			$logo = [];
			if(is_array($entityData['uploadsList'])) {
				foreach($entityData['uploadsList'] as $k => $upload) {
					if($upload['type'] != 0 && $upload['is_featured'] != 1) $gallery = true;
					if($upload['is_featured'] == 1) $logo = $upload;
					if($gallery && !empty($logo)) break;
				}
			}
			$entityData['gallery'] = $gallery;

			if(isset($entityData['user']) && is_array($entityData['user'])) {
				$user = $entityData['user'];
				$role = Role::find($user['role_id'])->name;
				$entityData['user'] = array_merge($user, User::getUserRelation($user['id'], $role));
				$entityData['user']['type'] = $role;
				$entityData['author_name'] = $user['first_name'].' '.$user['last_name'].' (ID '.$user['id'].')';
				if(empty($logo) && !empty($entityData['user']['photo'])) {
					$logo = Upload::getUploadById($entityData['user']['photo']);
				}
			}
			$entityData['logo'] = $logo;

			$entityData = static::_replaceLangFields($entityData);
			$franchiseData = static::getFranchiseData();
			foreach($franchiseData as $k => $d) {
				$entityData[$k . '_view'] = [];
				if(!empty($entityData[$k])) {
					foreach($d as $v) {
						if($v['id'] == $entityData[$k]) {
							$entityData[$k . '_view'] = $v;
						}
					}
				}
			}
			if(is_null($entityData['description'])) {
				$entityData['description'] = '';
			}
		} else {
			$user = Auth::user();
			if(!$user) return [];
			$entityData = [
				'lang_id' => CustomLaravelLocalization::getLocaleCode(),
				'status' => ($user->isAdmin() ? 1 : 2),
				'author' => $user->id,
				'user' => [],
			];
		}
		return $entityData;
	}

	public static function _replaceLangFields($entity) {
		foreach(static::$translatable as $field) {
			$name = 'lang_'.$field;
			if(isset($entity[$name])) {
				$entity[$field] = $entity[$name];
			}
		}
		return $entity;
	}
}
