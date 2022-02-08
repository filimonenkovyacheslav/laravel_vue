<?php

return [
	'driver' => env('MAIL_DRIVER', 'smtp'),
	'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
	'port' => env('MAIL_PORT', 587),
	'username' => env('MAIL_USERNAME'),
	'password' => env('MAIL_PASSWORD'),
	'encryption' => env('MAIL_ENCRYPTION', 'tls'),

	//templates
	'new_user' => 1,
	'new_agent' => 1,
	'approve_user' => 1,
	'reject_user' => 1,
	'new_property' => 1,
	'approve_property' => 1,
	'property_send_message' => 1,
	'new_franchise' => 1,
	'approve_franchise' => 1,
	'franchise_send_message' => 1,
	'agent_send_message' => 1,
	'contact_us' => 1,
	'get_quotes' => 1,
];
