<?php

namespace App\Http\Controllers\Emails;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Email;
use EmailLog;
use EmailTemplate;
use Setting;
use User;
use Validator;
use QuotesRequest;
use Response;

class EmailController extends Controller
{

	public function __construct()
	{
		$this->middleware('admin')->except(['sendEmailToAgent', 'sendContactEmail']);
	}

	public function getSettings(Request $request)
	{
		$data = $this->_presetData([
			'emails' => Setting::getValuesBySection('emails'),
			'fields' => Setting::_getFieldsList('emails'),
			]);
		return $this->showData($data);
	}

	public function getTemplate(Request $request, $params)
	{
		$data = $this->_presetData([
			'template' => EmailTemplate::get($params),
			'fields' => EmailTemplate::_getFieldsList(),
			]);
		return $this->showData($data);
	}

	public function getLog(Request $request)
	{
		$filter = $request->except('_token');
		if(!array_key_exists('filter_date', $filter)) {
			$filter['filter_date'] = date('Y-m-d');
		}

		$data = $this->_presetData([
			'log' => EmailLog::getLog($filter),
			'templates' => EmailTemplate::getTitles(),
			'filter' => $filter,
			]);
		return $this->showData($data);
	}

	public function saveEmailSettings(Request $request) {
		Setting::saveSection('emails', $request->except('_token'));

		return redirect()->back();
	}

	public function saveEmailTemplate(Request $request) {
		EmailTemplate::saveTemplate($request->all());

		return redirect()->back();
	}

	/*public function controlUserMail($attributes) {
		if (isset($attributes['user_email'])) {
			if (empty($attributes['user_data'])) {
				return false;
			}
			$user_data = explode(' ', decrypt($attributes['user_data']));
			if (count($user_data) != 2 || !is_numeric($user_data[1]) || strlen($user_data[0]) == 0) {
				return false;
			}
			$id = $user_data[1];
			$slug = $user_data[0];
			$user = User::find($id);
			if ($user && $user->slug == $slug && $user->email == $attributes['user_email']) {
				return true;
			}
			return false;
		}
		return true;
	}*/

	public function sendEmailToAgent(Request $request) {
		$model = $this->getModel();
		$attributes = $request->post();

		if(empty($attributes['user_slug']) || !empty($attributes['user_email'])) {
			return redirect()->back()->withErrors(['Error Data']);
		}

		/*if(!$this->getUserMail($attributes)) {
			return redirect()->back()->withErrors(['Error Data']);
		}*/

		$validator = Validator::make($attributes, [
			'from_name' => 'required',
			'from_email'=>'required|email',
			'message' => 'required',
			'g-recaptcha-response' => 'required|captcha'],
			[
				'from_name.required' => __('Your Name is required'),
				'from_email.required' => __('Email is required'),
				'message.required' => __('Message is required'),
				'g-recaptcha-response.required' => __('Please verify that you are not a robot')
			]);
		if($validator->fails()) {
			return redirect()->back()->withInput()->withErrors($validator);
		}
		$mailData = User::getUserMail($attributes['user_slug']);
		if (!is_array($mailData)) {
			return redirect()->back()->withErrors(['Error Data Mail']);
		}
		$attributes = array_merge($attributes, $mailData);

		if(empty($attributes['user_email']) || strpos($attributes['user_email'], 'info@medicaleer') === 0) {
			$attributes['user_email'] = config('app.email');
		} else {
            $attributes['send_to_user'] = 1;
            $attributes['copy_to'] = config('app.email');
        }
		$attributes['entity_url'] = url($attributes['entity_permalink']);
		$model::send($attributes['email_template'], $attributes);

		return redirect($attributes['entity_permalink'])->with('message', json_encode(__('Email sent successfully.')));
	}

	public function sendContactEmail(Request $request) {
		$model = $this->getModel();
		$attributes = $request->post();
		$validator = Validator::make($attributes, [
			'last_name' => 'required',
			'email'=>'required|email',
			'message' => 'required',
			'g-recaptcha-response' => 'required|captcha',
		], ['g-recaptcha-response.required' => __('Please verify that you are not a robot')]);
		if($validator->fails()) {
			return redirect()->back()->withInput()->withErrors($validator);
		}

		$attributes['user_email'] = config('app.email');

		$model::send('contact_us', $attributes);

		return redirect()->back()->with('message', json_encode(__('Email sent successfully.')));
	}

	public function sendQuoteEmail(Request $request) {
		$model = $this->getModel();
		$attributes = $request->post();
		if(isset($attributes['quote_when']) && $attributes['quote_when'] == 'date') {
			$attributes['quote_when'] = null;
		}

		QuotesRequest::addQuotesRequest($request);

		$model::send('get_quotes', $attributes);

		return Response::json([
			'message' => 'Done'
		], 200);
	}


}
