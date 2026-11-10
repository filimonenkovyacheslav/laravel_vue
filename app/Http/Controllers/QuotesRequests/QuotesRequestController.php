<?php

namespace App\Http\Controllers\QuotesRequests;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use App\Http\Controllers\Controller;
use Franchise;
use QuotesRequest;
use UploadFranchise;
use Feature;
use Response;
use Email;
use User;
use Auth;
use Quote;
use Country;
use Setting;
use CustomLaravelLocalization;

class QuotesRequestController extends Controller
{
    public $model;
	public $tableKey = 'id';

    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['getAllQuotesRequest']]);
	}

	public function getAllQuotesRequest(Request $request)
	{
		$model = $this->getModel();
		$orderBy = $model::getSortOrder($request);
		$data = $this->_presetData([
			'entity_type' => 'quotes_request',
            'entities' => $model::getAll(static::getParamsFromRequest($request), ['user_id'], $orderBy['order_by'], $orderBy['order']),
        ]);
		//dd($data);
		return $this->showData($data);
	}

	public function editQuotesRequest(Request $request, $param = null)
	{
		$model = $this->getModel();

		$quotesRequest = $model::where('quotes_requests.id', $param)->first();

		if(!is_null($param)) {
			$user = Auth::user();
			if ( !$user->isAdmin() ) {
				return redirect(url('/'));
			}
		}

		$quotes = Quote::pluck('id', 'phrase')->all();
		$statusesList = array();
		$statuses = QuotesRequest::getQuotesRequestsData('status');
		foreach($statuses as $status) {
			$statusesList[$status['id']] = $status['label'];
		}

		$data = $this->_presetData([
            'entity' => $quotesRequest,
			'categories' => $quotes,
			'statuses' => $statusesList,
        ]);
		return $this->showData($data);
	}

	public function _getQuotesRequest(Request $request, $param = null)
	{
		$model = $this->getModel();
		$entity = $model::getByParam('id', $param);
		//dd($entity);
		return Response::json(['entity' => $entity], 200);
	}

	public function deleteQuotesRequest (Request $request, $id)
	{
		return $this->setQuotesRequestStatus($request, $id, 5);
	}

	public function saveQuotesRequest(Request $request)
	{
		$model = $this->getModel();
		$user = $request->user();
		if ( !$user->isAdmin() ) {
			return Response::json(['message' => __('You have not permission'), 'errors_exist' => true, 'errors' => []], 200);
		}
		$oldUserId = QuotesRequest::where('id', $request->id)->pluck('user_id')->first();
		$result = $model::saveItem($request);
		if($result && is_array($result) && empty($result['errors'])) {
			if(is_null($request->id) || empty($request->id)) {
				return Response::json(['message' => __('New Quotes Request was created. Wait for approval.'), 'id' => $result['id'], 'redirect' => route('quotesRequest.edit.admin', ['id' => $result['id']]), 'errors_exist' => false], 200);
			}
			$template['info'] = $result['info'];
			$newUserId = $request->user_id;

			if ($oldUserId != $newUserId) {
				$user = User::getUserById( $result['user_id'] );
				$template['user_email'] = $user['email'];
				$template['user_name'] = $user['first_name'].' '.$user['last_name'];
				$template['link'] = '<a href="'.url('/').'/admin/profile/quotesRequests">Check all Quotes Requests</a>';
				Email::send('send_quotes_request', $template);
			}

			return Response::json(['message' => __('Done'), 'id' => $result['id'], 'entity' => $model::getByParam('id', $result['id']), 'errors_exist' => false], 200);
		}
		return Response::json(['message' => __('Not all required fields are filled'), 'errors_exist' => true, 'errors' => $result['errors']], 200);
	}

	public function setQuotesRequestStatus(Request $request, $id, $status)
	{
		$model = $this->getModel();
		$quotesRequest = $model::setQuotesRequestStatus($id, $status);

		if($quotesRequest === false) {
			return redirect(route('user.profile.quotesRequests'));
		}

		if(isset($quotesRequest['status']) && $quotesRequest['status'] == 1) {
			// $attributes = [
			// 	'entity_id' => $quotesRequest['id'],
			// 	'entity_info' => $quotesRequest['info'],
			// 	'entity_category' => $quotesRequest['quotes_id'],
			// ];
			// $user = User::findOrFail($quotesRequest['user_id']);
			// foreach($user->toArray() as $key => $value) {
			// 	if(!is_null($value) && !is_array($value)) {
			// 		$attributes['user_' . $key] = $value;
			// 	}
			// }
			// Email::send('approve_franchise', $attributes);
		}
		return redirect()->back();
	}
}
