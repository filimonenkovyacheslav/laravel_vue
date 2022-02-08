<?php

namespace App\Http\Models\QuotesRequests;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Auth;
use DB;
use CustomLaravelLocalization;
use Validator;
use User;
use Upload;
use Quote;
use SearchHelper;
use ElasticSearchHelper;
use Country;
use Role;
use Setting;

class QuotesRequest extends \App\Http\Models\BaseModel
{
	public $fillable = [
		'quotes_id', 'info', 'user_id', 'status'
	];
	public static $listRoute = 'user.profile.quotesRequests';
	public static $type = 'quotes_request';
	public static $tableName = 'quotes_requests';

	public static function getQuotesRequestsData($key = '') {
		$quotesRequestsData = [
			'status' => [
				'publish' => ['id' => 1, 'label' => __('Published')],
				'pending' => ['id' => 2, 'label' => __('Pending')],
				'deleted' => ['id' => 5, 'label' => __('Deleted')],
			],
		];
		return empty($key) ? $quotesRequestsData : $quotesRequestsData[$key];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function quote()
	{
		return $this->belongsTo(Quote::class, 'quotes_id');
	}

	public static function _canQuotesRequestEdit($quotesRequest, $adminOnly = false) {
		$user = Auth::user();
		$admin = $user->isAdmin();

		if($adminOnly && !$admin) return false;

		if ( !$quotesRequest || !$admin ) {
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
		$entities->orderBy($orderBy, $order);
		$pagination = static::_addTranslation($entities)->paginate(static::$pagination);
		$pagination->getCollection()->transform(function ($entity) {
			return static::_afterGet($entity);
		});
		return $pagination;
	}

	public static function getByParam($param, $value, $with = ['user'], $status = null)
	{
		$entity = static::where('quotes_requests.'.$param, $value)->with($with)->first();
		if(!is_null($status)) {
			$entity->where('status', $status);
		}
		$entity = static::_afterGet($entity);
		//$entity['user'] = isset($entity['user']) ? User::_afterGet($entity['user']) : null;

		return $entity;
	}

	public static function saveItem($request, $preset = false, $langsData = []) {
		$data = !$preset ? $request->all() : $request;

		$validator = Validator::make($data, [
			'id' => 'required',
		])->setAttributeNames([
			'id' => __('Id'),
		]);
		if($validator->fails()) {
			return ['errors' => $validator->errors()->toArray()];
		}

		$id = (isset($data['id']) && !empty($data['id']) ? $data['id'] : null);
		$new = is_null($id);
		$entity = static::findOrCreate($id);

		if(!$preset && !$new && !static::_canQuotesRequestEdit($entity)) {
			return redirect(url('/'));
		}

		$entity->fill($data);
		$entity->save();
		$id = $entity->id;

		$entity = $entity->toArray();

		if(!$preset && Auth::user()->isAdmin()) {
			$user = User::find($entity['user_id']);
		}

		return $entity;
	}

	public static function setQuotesRequestStatus($id, $status)
	{
		$quotesRequest = static::find($id);

		if(!static::_canQuotesRequestEdit($quotesRequest, $status == 1)) {
			return redirect(url('/'));
		}
		if($quotesRequest->status != $status) {
			$quotesRequest->fill(['status' => $status])->save();
			return $quotesRequest->toArray();
		}
		return true;
	}

	public static function getQuotesTitleById($id, $reverse = false) {
		if (!$reverse) {
			$quote = DB::table('quotes')->where('id', $id)->first();
			$title = !empty($quote) ? $quote->phrase : '';
		} else {
			$quote = DB::table('quotes')->where('phrase', $id)->first();
			$title = !empty($quote) ? $quote->id : 0;
		}
		return $title;
	}

	public static function addQuotesRequest($request) {
		$data = $request->post();
		if (!empty($data)) {
			$id = (isset($data['id']) && !empty($data['id']) ? $data['id'] : null);
			$new = is_null($id);
			$entity = static::findOrCreate($id);
			if(!$new && !static::_canQuotesRequestEdit($entity)) {
				return redirect(url('/'));
			}
			$quotesRequest = new QuotesRequest;
			$quotesRequest->quotes_id = static::getQuotesTitleById($data['quote_what'], true);
			$quotesRequest->info = 'What: '.$data['quote_what'].'<br>';
			$quotesRequest->info .= 'Where: '.$data['quote_where'].'<br>';
			$quotesRequest->info .= 'When: '.$data['quote_when'].$data['quote_date'].'<br>';
			//$quotesRequest->info .= 'Budget: '.$data['quote_budget'].'<br>';
			$quotesRequest->info .= 'About: '.$data['quote_about'].'<br>';
			$quotesRequest->info .= 'First Name: '.$data['quote_fname'].'<br>';
			$quotesRequest->info .= 'Last Name: '.$data['quote_lname'].'<br>';
			$quotesRequest->info .= 'Email: '.$data['quote_email'].'<br>';
			$quotesRequest->info .= 'Phone: '.$data['quote_phone'].'<br>';
			$quotesRequest->user_id = 1;
			$quotesRequest->status = 2;
			$quotesRequest->save();
		}
	}

	public static function _afterGet($entity, $role = null, $relation = null)
	{
		$entityData = $entity;

		if(!empty($entityData)) {
			$entityData = !is_array($entityData) ? $entityData->toArray() : $entityData;
			$requestDataStatus = static::getQuotesRequestsData('status');
			foreach ($requestDataStatus as $key => $status) {
				if ($status['id'] == $entityData['status']) {
					$entityData['status_label'] = $status['label'];
				}
			}
			$entityData['quotes']['quotes_title'] = static::getQuotesTitleById($entityData['quotes_id']);
		}

		return $entityData;
	}
}
