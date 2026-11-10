<?php

namespace App\Http\Controllers\JobEntities;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JobEntity;
use JobEntityPrice;
use JobEntityFavorite;
use JobCategory;
use UploadJobEntity;
use Feature;
use Response;
use Email;
use Upload;
use User;
use Auth;
use AgencyAgents;
use Country;
use CustomLaravelLocalization;
use Measure;
use DateTime;
use Setting;

class JobEntityController extends Controller
{
    public $model;
	public $imagesModel;
	public $tableKey = 'id';

    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['getAllJobEntities', 'getJobEntityBySlug']]);
	}

	public function getAllJobEntities(Request $request)
	{
		$model = $this->getModel();
		$orderBy = $model::getSortOrder($request);
		$data = $this->_presetData([
			// 'entity_type' => 'job_entity',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user'], $orderBy['order_by'], $orderBy['order']),
        ]);

		$jobEntityData = JobEntity::getJobEntityData();
		$jobCategories = JobCategory::getAllList();

		foreach ($data['entities'] as $index => $value) {
			//Label for Job Type
			if (!empty($data['entities'][$index]['job_type'])) {
				$job_type = isset($data['entities'][$index]['job_type']) ? $data['entities'][$index]['job_type'] : '';
				$job_type = isset($jobEntityData['job_type'][$job_type-1]['label']) ? $jobEntityData['job_type'][$job_type-1]['label'] : '';
				$temp = $data['entities'][$index];
				$temp['job_type'] = $job_type;
				$data['entities'][$index] = $temp;
			}

			//Label for Job Salary
			if (!empty($data['entities'][$index]['job_salary_type']) && !empty($jobCategories)) {
				$job_salary_type = isset($data['entities'][$index]['job_salary_type']) ? $data['entities'][$index]['job_salary_type'] : '';
				$job_salary_type = isset($jobEntityData['job_salary_type'][$job_salary_type-1]['label']) ? $jobEntityData['job_salary_type'][$job_salary_type-1]['label'] : '';
				$temp = $data['entities'][$index];
				$temp['job_salary_type'] = $job_salary_type;
				$data['entities'][$index] = $temp;
			}

			//Format date from created_at
			if (!empty($data['entities'][$index]['created_at'])) {
				//$created_at = date("j M Y",strtotime($data['entities'][$index]['created_at']));
				$created_at = $this->timeElapsedString($data['entities'][$index]['created_at']);
				$temp = $data['entities'][$index];
				$temp['created_at'] = $created_at;
				$data['entities'][$index] = $temp;
			}

			//Get Job current category
			$job_categories = !empty($jobCategories) ? $jobCategories : array();
			if (!empty($job_categories)) {

        $categoryParentId = JobCategory::getCategoryParentId($data['entities'][$index]['job_category_id']);
        if (!empty($categoryParentId)) {
            $jobParentCategory = $job_categories[$categoryParentId];
        }

				$job_categories = $job_categories[$data['entities'][$index]['job_category_id']];

        $job_categories = !empty($jobParentCategory) ? $jobParentCategory.' > '.$job_categories : $job_categories;

				$temp = $data['entities'][$index];
				$temp['job_category'] = $job_categories;
				$data['entities'][$index] = $temp;
			}

			//Get Job Company Logo
			if (!empty($data['entities'][$index]['photo'])) {
				$photo = Upload::getUploadById($data['entities'][$index]['photo']);
				$photo = !empty($photo['name']) ? '/uploads/'.$photo['name'] : '/uploads/'.$photo['name'];
				$temp = $data['entities'][$index];
				$temp['photoImage'] = $photo;
				$data['entities'][$index] = $temp;
			}

		}

		//dd($data);
		return $this->showData($data);
	}

	public function getJobEntityBySlug(Request $request, $param)
	{
		$model = $this->getModel();
		$jobEntity = $model::getByParam('slug', $param, ['user', 'country']);
		if(!isset($jobEntity['id']) || $jobEntity['status'] == 5) return redirect('404');

		$jobEntityData = JobEntity::getJobEntityData();

		//Label for Job Type
		$job_type = isset($jobEntity['job_type']) ? $jobEntity['job_type'] : '';
		$job_type = isset($jobEntityData['job_type'][$job_type-1]['label']) ? $jobEntityData['job_type'][$job_type-1]['label'] : '';
		$jobEntity['job_type'] = $job_type;

		//Label for Job Salary
		$job_salary_type = isset($jobEntity['job_salary_type']) ? $jobEntity['job_salary_type'] : '';
		$job_salary_type = !empty($job_salary_type) && isset($jobEntityData['job_salary_type'][$job_salary_type-1]['label']) ? $jobEntityData['job_salary_type'][$job_salary_type-1]['label'] : '';
		$jobEntity['job_salary_type'] = $job_salary_type;

		//Format date from created_at
		$jobEntity['created_at'] = date("j M Y",strtotime($jobEntity['created_at']));

		//Get Job current category
		$jobCategories = JobCategory::getAllList();
		$job_categories = !empty($jobCategories) ? $jobCategories : array();

    $categoryParentId = JobCategory::getCategoryParentId($jobEntity['job_category_id']);
    if (!empty($categoryParentId)) {
      $jobEntity['job_parent_category'] = $job_categories[$categoryParentId];
    }

		$jobEntity['job_category'] = !empty($jobEntity['job_parent_category']) ? $jobEntity['job_parent_category'].' > '.$job_categories[$jobEntity['job_category_id']] : $job_categories[$jobEntity['job_category_id']];

		$jobEntity['photoImage'] = !empty($jobEntity['photo']) ? Upload::getUploadById($jobEntity['photo']) : [];
		$jobEntity['photoImage'] = !empty($jobEntity['photoImage']) ? '/uploads/'.$jobEntity['photoImage']['name'] : '';

		$data = $this->_presetData([
        'entity' => $jobEntity,
			  'entities_similar' => $model::getAll([
				'job_type' => $jobEntity['job_type'],
				'country' => $jobEntity['country'],
				'city' => $jobEntity['city'],
				'not_in' => [$jobEntity['id']],
			], ['user']),
    ]);
		//dd($data);
		return $this->showData($data);
	}

	public function editJobEntity(Request $request, $param = null)
	{
		$model = $this->getModel();
		$jobEntity = $model::_addTranslation($model::where('job_entities.id', $param))->first();

		if(!is_null($param)) {
			$user = Auth::user();
			if(!isset($jobEntity['author']) || ($user->id != $jobEntity['author'] && !$user->isAdmin() && AgencyAgents::getAgencyId($jobEntity['author']) != $user->id)) {
				return redirect(url('/'));
			}
		}
		$data = $this->_presetData([
            'id' => $param,
			'measures' => Measure::getMeasuresForSelect(),
		]);
		$jobEntityData = JobEntity::getJobEntityData();

		$jobCategories = JobCategory::getAllList();
		$data['job_categories'] = !empty($jobCategories) ? $jobCategories : array();

		foreach($jobEntityData['job_type'] as $i => $entityData) {
			$data['job_entity_types'][$entityData['id']] = $entityData['label'];
		}
		foreach($jobEntityData['job_salary_type'] as $i => $entityData) {
			$data['job_entity_salary_types'][$entityData['id']] = $entityData['label'];
		}
		foreach(Country::all() as $i => $country) {
			$data['countries'][$country->id] = $country->name;
			$data['countries_codes'][$country->iso2] = $country->id;
		}

		return $this->showData($data);
	}

	public function _getJobEntity(Request $request, $param = null)
	{
		$model = $this->getModel();
		$entity = $model::getByParam('id', $param);
		//dd($entity);
		return Response::json(['entity' => $entity], 200);
	}

	public function deleteJobEntity(Request $request, $id)
	{
		return $this->setJobEntityStatus($request, $id, 5);
	}

	public function canUserAddJob($userId, $userRoleId)
	{
		$model = $this->getModel();
		$count = $model::getCountJobEntitiesByUser($userId);
		$jobSettings = $model::getJobSettings();
		$roleInArr = in_array($userRoleId, ['8','15']);
		$limit = isset($jobSettings['job_entity']['job_limit']) ? $jobSettings['job_entity']['job_limit'] : 10;
		$limitByUser = User::getLimitJobEntitiesByUser($userId);
		$limit = !empty($limitByUser[0]) ? $limitByUser[0] : $limit;
		$canUsersAddJob = isset($jobSettings['job_entity']['job_user_add']) && $jobSettings['job_entity']['job_user_add'] === 'on' ? true : false;
		if ($canUsersAddJob && $roleInArr) {
			if ($count < $limit) {
				return true;
			}
		}
		return false;
	}

	public function saveJobEntity(Request $request)
	{
		$model = $this->getModel();
		$user = $request->user();
		if ( !$user->isAdmin() ) {
			if ( !$this->canUserAddJob($user['id'], $user['role_id']) ) {
				return Response::json(['message' => __('You have exceeded the limit for adding jobs'), 'errors_exist' => true, 'errors' => []], 200);
			}
		}

		$result = $model::saveItem($request);
		if($result && is_array($result) && empty($result['errors'])) {
			if(is_null($request->id) || empty($request->id)) {
				$attributes = [
					'entity_url' => url(route('jobEntity.view.frontend', ['slug' => $result['slug']])),
					'entity_title' => $result['title']
				];
				Email::send('new_job', $attributes);
				return Response::json(['message' => __('New JobEntity was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('jobEntity.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
			}
			return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
		}
		return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
	}

	public function saveJobSettings(Request $request)
	{
		$model = $this->getModel();
		Setting::saveSection('job_entity', $request->except('_token'));
		return redirect()->back();
	}

	public function getJobSettings()
	{
		$model = $this->getModel();
		$jobSettings = $model::getJobSettings();
		return $jobSettings;
	}

	public function setJobEntityStatus(Request $request, $id, $status)
	{
		$model = $this->getModel();
		$jobEntity = $model::setJobEntityStatus($id, $status);

		if($jobEntity === false) {
			return redirect(route('user.profile.jobEntities'));
		}

		if(isset($jobEntity['status']) && $jobEntity['status'] == 1) {
			$attributes = [
				'entity_url' => url(route('jobEntity.view.frontend', ['slug' => $jobEntity['slug']])),
				'entity_title' => $jobEntity['title'],
				'edit_url' => url(route('jobEntity.edit.admin', ['id' => $jobEntity['id']])),
			];
			$user = User::findOrFail($jobEntity['author']);
			foreach($user->toArray() as $key => $value) {
				if(!is_null($value) && !is_array($value)) {
					$attributes['user_' . $key] = $value;
				}
			}
			Email::send('approve_jobEntity', $attributes);
		}
		return redirect()->back();
	}

	public function setJobEntityLabel(Request $request, $id, $label)
	{
		$model = $this->getModel();
		$model::setJobEntityLabel($id, $label);

		return redirect()->back();
	}

	public function toggleFavoriteJobEntity(Request $request)
	{
		$favorite = JobEntityFavorite::toggleFavoriteJobEntity($request->except('_token'));

		return Response::json(['message' => 'Done', 'favorite' => $favorite], 200);
	}

	public function updateJobEntityPrices() {
		JobEntityPrice::updatePrices();
	}

	public function getImagesModel() {
		if(empty($this->imagesModel)) {
			$this->imagesModel = UploadJobEntity::class;
		}
		return $this->imagesModel;
	}
	public function timeElapsedString($datetime, $full = false) {

	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => __('year'),
	        'm' => __('month'),
	        'w' => __('week'),
	        'd' => __('day'),
	        'h' => __('hour'),
	        'i' => __('minute'),
	        's' => __('second'),
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? __('s') : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' '.__('ago') : __('just now');
	}
}
