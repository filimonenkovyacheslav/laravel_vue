<?php

namespace App\Http\Models\JobEntities;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use JobEntityPrice;
use Auth;
use DB;
use Currency;
use CurrencyConverter;
use Money;
use Measure;
use CustomLaravelLocalization;
use Validator;
use User;
use Upload;
use Setting;
use Feature;
use SearchHelper;
use ElasticSearchHelper;
use Country;
use Role;
use AgencyAgents;
use JobEntitiesFloors;
use JobEntityLang;

class JobEntity extends \App\Http\Models\BaseModel
{
	use Sluggable;

	protected $table = 'job_entities';

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

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	public $fillable = [
		'slug', 'author',
		'title', 'description', 'short_description', 'status', 'label', 'photo',
		'job_type', 'job_subtype', 'job_category_id', 'company_name',
		'price_default', 'price_local', 'price_second', 'currency_code', 'price_before', 'price_after', 'price_hidden', 'job_salary_type',
		'address', 'postal_code', 'country', 'state', 'city', 'neighborhood', 'map_address', 'lat', 'lng',
	];
	public $casts = [
		'price_hidden' => 'boolean',
	];
	public static $type = 'job_entity';
	public static $tableName = 'job_entities';
	public static $langTable = 'job_entity_langs';
	public static $langKey = 'job_entity_id';
	public static $langFields = 'job_entity_langs';

	public static function getJobEntityData($key = '') {
		$jobEntityData = [
			'status' => [
				'publish' => ['id' => 1, 'label' => __('Published')],
				'pending' => ['id' => 2, 'label' => __('Pending')],
				'expired' => ['id' => 3, 'label' => __('Expired')],
				'draft' => ['id' => 4, 'label' => __('Draft')],
				'deleted' => ['id' => 5, 'label' => __('Deleted')],
			],
			'label' => [
				'premium' => ['id' => 1, 'label' => __('Premium'), 'color' => 'blue'],
				'featured' => ['id' => 2, 'label' => __('Featured'), 'color' => 'orange'],
			],
			'job_type' => [
				['id' => 1, 'label' => __('Full time')],
				['id' => 2, 'label' => __('Part time')],
				['id' => 3, 'label' => __('Contract / Temporary')],
				['id' => 4, 'label' => __('Casual / Vacation')],
			],
			'job_salary_type' => [
				['id' => 1, 'label' => __('annually')],
				['id' => 2, 'label' => __('per hour')],
			],
		];

		return empty($key) ? $jobEntityData : $jobEntityData[$key];
	}

	public static $translatable = [
		'title', 'description', 'address',
	];
	public static $hasArea = [
		'property_area', 'land_area', 'garage_area',
	];

	public function getDescriptionAttribute($value) {
		return !empty($value) ? $value : '';
	}

	public function user()	{
		return $this->belongsTo(User::class, 'author');
	}

	public function country() {
      return $this->belongsTo(Country::class, 'country');
    }

	public static function _addTranslation($query, $keepSelect = false)
	{
		$defLang = static::getDefaultLang();
		$langId = CustomLaravelLocalization::getLocaleCode();
		if($langId == $defLang) return $query;

		$defTable = 'job_entities';
		$langTable = 'job_entity_langs';
		$fieldPrefix = 'lang_';
		$translatable = static::$translatable;

		$query = static::replaceQuery($query, $translatable, $defTable, $langTable, $fieldPrefix);

		if(!$keepSelect) {
			$query->select($defTable.'.*');
		}

		$query->leftJoin($langTable, function ($join) use($defTable, $langTable, $langId) {
				$join->on($langTable.'.job_entity_id', '=', $defTable.'.id')
					->where($langTable.'.lang_id', '=', $langId);
			})
			->addSelect(DB::raw(static::getLangFieldsList($translatable, $defTable, $translatable, $langTable, $fieldPrefix)));

		return $query;
	}

	public static function _canJobEntityEdit($jobEntity, $adminOnly = false) {
		$user = Auth::user();
		$admin = $user->isAdmin();
		if($adminOnly && !$admin) return false;

		if(!$jobEntity || !isset($jobEntity->author)) {
			return false;
		}

		if(!$admin && $user->id != $jobEntity->author && AgencyAgents::getAgencyId($jobEntity->author) != $user->id) {
			return false;
		}

		/*if(!$jobEntity || !isset($jobEntity->author) || ($user->id != $jobEntity->author && !$admin)) {
			return false;
		}*/
		return true;
	}

	public static function getAll($params = [], $with = [], $orderBy = 'id', $order = 'asc')
	{
		$defaultOrder = is_null($orderBy) || empty($orderBy);
		$orderBy = !empty($orderBy) ? $orderBy : 'price_default';
		$order = !empty($order) ? $order : 'desc';

		$entities = static::query();

		if(!empty($params)) {
			$prefix = 'job_entities.';

			$entities = SearchHelper::applyCommonSearchParams($entities, $params);

			foreach($params as $k => $v) {
				switch($k) {
					case 'job_status':
					case 'job_type':
						if(is_numeric($v)) {
							$entities = SearchHelper::applyWhere($entities, $prefix . $k, $v);
						}
						break;
					case 'price':
						if(array_filter($v)) {
							$entities = SearchHelper::applyJobEntityPriceParam($entities, $prefix . $k . '_default', $v, $params['currency_code']);
						}
						break;
					default:
						break;
				}
			}
			//dd($params, $entities->toSql(), $entities->get());
		}
		$entities->where('status', 1)->with($with);
		if($defaultOrder) {
			$entities->orderBy('job_entities.label', 'asc')->orderBy($orderBy, $order);
		} else {
			$entities->orderBy($orderBy, $order)->orderBy('job_entities.label', 'asc');
		}
		$pagination = static::_addTranslation($entities)->paginate(static::$pagination);
		//$pagination = static::_addTranslation($entities->where('status', 1)->with($with)->orderBy($orderBy, $order)->orderBy('jobEntitys.label', 'asc'))->paginate(static::$pagination);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity);
		});
		return $pagination;
	}

	public static function getByParam($param, $value, $with = ['user'])
	{
		$entity = static::_addTranslation(static::where('job_entities.'.$param, $value))->with($with)->first();
		$entity = static::_afterGet($entity);
		//dd($entity);
		$entity['user'] = isset($entity['user']) ? User::_afterGet($entity['user'], isset($entity['user']['type']) ? $entity['user']['type'] : null, 1) : null;

		return $entity;
	}

	public static function getCountJobEntitiesByUser($userId)
	{
		$count = static::where('job_entities.author', '=', $userId)->whereIn('job_entities.status', ['1', '2'])->count();
		return $count;
	}

	public static function getJobSettings()
	{
		$data = array(
			'job_entity' => Setting::getValuesBySection('job_entity', false),
			'fields' => Setting::_getFieldsList('job_entity'),
		);
		return $data;
	}

	public static function saveItem($request, $preset = false, $langsData = []) {
		$data = !$preset ? $request->all() : $request;
		$validator = Validator::make($data, [
			'title' => 'required',
			'description' => 'required',
			'short_description' => 'required',
			'job_type' => 'required',
			'company_name' => 'required',
			'job_category_id' => 'required',
			//'price' => 'required|numeric|min:1',
		])->setAttributeNames([
			'title' => __('Title'),
			'description' => __('Description'),
			'short_description' => __('Description'),
			'job_type' => __('Job Type'),
			'company_name' => __('Company Name'),
			'job_category_id' => __('Job Category'),
			//'price' => __('Price'),
		]);
		if($validator->fails()) {
			return ['errors' => $validator->errors()->toArray()];
		}
		$id = (isset($data['id']) && !empty($data['id']) ? $data['id'] : null);
		$new = is_null($id);
		$entity = static::findOrCreate($id);

		$user = Auth::user();
		if(!$preset && !$user->isAdmin() && $data['author'] != $user->id) {
			if(AgencyAgents::getAgencyId($data['author']) != $user->id) {
				return ['message' => __('This is not your agent.'), 'errors' => []];
			}
		}

		if(!$preset && !$new && !static::_canJobEntityEdit($entity)) {
			return redirect(url('/'));
		}

		$data['slug'] = null;
		// Boolean fields
		$data['price_hidden'] = !empty($data['price_hidden']);
		// Additional actions
		$data = JobEntityPrice::calculatePrice($data);

		$defLang = static::getDefaultLang();
		$langId = $preset && isset($data['langId']) ? $data['langId'] : CustomLaravelLocalization::getLocaleCode();
		//dd($data, $id, $entity);
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
		//dd($defLang, $langId, $langData);
		$data = Upload::attachUploads($data, $request, ['photoNew']);
		$data['photoNew'] = ( empty($data['photoNew']) || $data['photoNew'] === 'undefined') ? 0 : $data['photoNew'];
		$data['photo'] = ( empty($data['photoNew']) ) ? $data['photo'] : $data['photoNew'];

		$entity->fill($data);
		$entity->save();
		$id = $entity->id;
		if(isset($langsData) && sizeof($langsData) > 0) {
			foreach($langsData as $langId => $langData) {
				$lang = JobEntityLang::where([['job_entity_id', $id], ['lang_id', $langId]])->first();
				if(!$lang) {
					$langData['job_entity_id'] = $id;
					$langData['lang_id'] = $langId;
					$lang = new JobEntityLang;
				}
				$lang->fill($langData)->save();
				ElasticSearchHelper::updateElasticEntity($lang, 'job_entity');
			}
		} else {
			ElasticSearchHelper::updateElasticEntity($entity, 'job_entity');
		}
		Upload::saveUploadedImages(isset($data['photos']) ? $data['photos'] : [], $id, static::$tableName);
		// Upload::makeImageFeatured($id, static::$tableName, isset($data['featured_image']) ? $data['featured_image'] : null);
		// Feature::saveJobEntityFeatures($id, isset($data['features']) ? $data['features'] : []);
		// JobEntitiesFloors::saveJobEntityFloors($id, $data['floors']);

		$entity = $entity->toArray();

		if(!$preset/* && Auth::user()->isAdmin()*/) {
			$user = User::find($entity['author']);
			$entity['author_name'] = $user->first_name.' '.$user->last_name.' (ID '.$user->id.')';
		}
		//dd($entity);

		return $entity;
	}

	public static function setJobEntityStatus($id, $status)
	{
		$jobEntity = static::find($id);

		if(!static::_canJobEntityEdit($jobEntity, $status == 1)) {
			return redirect(url('/'));
		}
		if($jobEntity->status != $status) {
			$jobEntity->fill(['status' => $status])->save();
			return $jobEntity->toArray();
		}
		return true;
	}

	public static function setJobEntityLabel($id, $label)
	{
		$jobEntity = static::find($id);

		if(!static::_canJobEntityEdit($jobEntity, true)) {
			return redirect(url('/'));
		}
		$labels = static::getJobEntityData('label');
		$labelId = isset($labels[$label]) ? $labels[$label]['id'] : null;

		if($jobEntity->label != $labelId) {
			$jobEntity->fill(['label' => $labelId])->save();
		}
		return true;
	}

	public static function _afterGet($entity, $role = null, $relation = null)
	{
		$entityData = $entity;

		if(!empty($entityData)) {
			$entityData = !is_array($entityData) ? $entityData->toArray() : $entityData;

			if(isset($entityData['user']) && is_array($entityData['user'])) {
				$user = $entityData['user'];
				$role = Role::find($user['role_id'])->name;
				$entityData['user'] = array_merge($user, User::getUserRelation($user['id'], $role));

				$entityData['user']['type'] = $role;
				$entityData['author_name'] = $user['first_name'].' '.$user['last_name'].' (ID '.$user['id'].')';
			}

			$id = $entityData['id'];
			$entityData['is_favorite'] = JobEntityFavorite::isJobEntityFeatured($id);
			$entityData['uploadsList'] = Upload::getUploadedImages($id, static::$tableName);
			$entityData['uploadsTypes'] = array();
			$entityData['photoImage'] =  !empty($entityData['photo']) ? Upload::getUploadById($entityData['photo']) : [];
			$entityData['pdfList'] = array();
			foreach($entityData['uploadsList'] as $upload) {
				if(!in_array($upload['type'], $entityData['uploadsTypes'])) {
					array_push($entityData['uploadsTypes'], $upload['type']);
				}
				if ($upload['type'] == 0) {
					$entityData['pdfList'][] = $upload['name'];
				}
			}
			$entityData = static::_replaceLangFields($entityData);
			$jobEntityData = static::getJobEntityData();
			foreach($jobEntityData as $k => $d) {
				$entityData[$k . '_view'] = [];
				if(!empty($entityData[$k])) {
					foreach($d as $v) {
						if($v['id'] == $entityData[$k]) {
							$entityData[$k . '_view'] = $v;
						}
					}
				}
			}
			$entityData = JobEntityPrice::preparePriceToView($entityData);
		} else {
			$user = Auth::user();
			if(!$user) return [];
			$measure = CustomLaravelLocalization::getDefaultAreaMeasureCode();
			$entityData = [
				'lang_id' => CustomLaravelLocalization::getLocaleCode(),
				'status' => ($user->isAdmin() ? 1 : 2),
				'author' => $user->id,
				'job_status' => '',
				'job_type' => '',
				'photoImage' => [],
				'currency_code' => JobEntityPrice::$defaultCurrencyCode,
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
