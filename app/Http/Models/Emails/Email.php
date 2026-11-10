<?php

namespace App\Http\Models\Emails;

use Illuminate\Database\Eloquent\Model;
use Mail;
use EmailTemplate;
use EmailLog;
use Setting;
use User;
use Swift_SmtpTransport;

class Email extends Model
{
	public static function send($type, $variables = array()) {
		$settings = Setting::getValuesBySection('emails');
		//dd($settings, $settings[$type]);
		if(!$settings || !isset($settings[$type]) || $settings[$type] != 1) return false;
		if(!isset($settings['driver'])) return false;

		$template = EmailTemplate::get($type);
		$variables = array_merge($variables, static::getMailVariables());

		$data = str_replace(array_map(function($n) {return '{'.$n.'}';}, array_keys($variables)), array_values($variables), $template);
		$body = $data['greeting'] . PHP_EOL . $data['body'] . PHP_EOL . $data['signature'];
		$data['body'] = url('/') . '<br><br>' . nl2br($data['body'], false);
		$data['signature'] = nl2br($data['signature'], false);

		if(empty($data['to_address']) || empty($data['from_address'])) {
			return false;
		}
		if(!isset($variables['send_to_user'])) {
			$data['to_address'] = config('app.email');
		}
        if(isset($variables['copy_to'])) {
            $data['copy_to'] = $variables['copy_to'];
            if (preg_match("/(\r|\n)/i", $data['copy_to'])) {
            	return false;
            }
        }

		$driver = isset($settings['driver']) ? $settings['driver'] : '';

		if (preg_match("/(\r|\n)/i", $data['to_address']) || preg_match("/(\r|\n)/i", $data['to_name']) || preg_match("/(\r|\n)/i", $data['from_address']) || preg_match("/(\r|\n)/i", $data['from_name']) || preg_match("/(\r|\n)/i", $data['subject'])) {
			return false;
		}

        if($driver == 'smtp'){
        	//dd($settings);
        	$transport = new Swift_SmtpTransport($settings['host'], $settings['port'], $settings['encryption']);
		    $transport->setUsername($settings['username']);
	        $transport->setPassword($settings['password']);
        	$smtp = new \Swift_Mailer($transport);
        	Mail::setSwiftMailer($smtp);
        }

		Mail::send('emails.mail', $data, function($message) use ($data) {
			$message->to($data['to_address'], $data['to_name'])->subject($data['subject']);
            if(isset($data['copy_to'])) {
                $message->bcc($data['copy_to']);
            }
			$message->from($data['from_address'], $data['from_name']);
		});

		$log = new EmailLog();
		$log->fill([
			'name' => $type,
			'from' => $data['from_name'] . ' <' . $data['from_address'] . '>',
			'to' => $data['to_name'] . ' <' . $data['to_address'] . '>',
			'subject' => $data['subject'],
			'body' => $body,
			'success' => count(Mail::failures()) > 0 ? 0 : 1,
		])->save();
	}

	private static function getMailVariables() {
		return [
			'admin_email' => config('app.email'),
			'admin_name' => 'Admin {site_name}',
			'site_name' => config('app.name'),
			'login_url' => url('login'),
			'approve_user' => url(route('user.profile.users', array('params' => 0))),
			'approved_property' => url(route('user.profile.properties', array('params' => 'pending'))),
			// 'approved_franchise' => url(route('user.profile.franchises', array('params' => 'pending')))
		];
	}
}
