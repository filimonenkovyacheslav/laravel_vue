<?php
// Main capture secret and sitkey are placed in .env file:
// NOCAPTCHA_SECRET=6LdnBHoUAAAAAFx7afbNuivn718PIKnQTzsZv1XA
// NOCAPTCHA_SITEKEY=6LdnBHoUAAAAAE06NhMZ6nmMqV6zF16JJmyKEQJs
return [
    'secret' => (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], [
    		'medicaleer.africa',
    		'medicaleer.com.au',
    		'medicaleer.com.ar',
    		'medicaleer.com.br',
    		'medicaleer.co.nz',
    		'medicaleer.al',
    		'medicaleer.mk',
    		'medicaleer.my',
    		'medicaleer.mt',
    		'medicaleer.mx',
    		'medicaleer.co.za',
    		'medicaleer.tw',
    		'medicaleer.ke',
    		'medicaleer.ug',
            'medicaleer.eu',
    		'medicaleer.ph']) ? '6Lc4-noUAAAAAAOvl9NcIsg123ow3PF68XhWO1fl' : env('NOCAPTCHA_SECRET')),
    'sitekey' => (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], [
    		'medicaleer.africa',
    		'medicaleer.com.au',
    		'medicaleer.com.ar',
    		'medicaleer.com.br',
    		'medicaleer.co.nz',
    		'medicaleer.al',
    		'medicaleer.mk',
    		'medicaleer.my',
    		'medicaleer.mt',
    		'medicaleer.mx',
    		'medicaleer.co.za',
    		'medicaleer.tw',
    		'medicaleer.ke',
    		'medicaleer.ug',
            'medicaleer.eu',
    		'medicaleer.ph']) ? '6Lc4-noUAAAAADT4GY8HPVO9cJqc5rSPh5aXiGRr' : env('NOCAPTCHA_SITEKEY')),
    'options' => [
        'timeout' => 30,
    ],
];
