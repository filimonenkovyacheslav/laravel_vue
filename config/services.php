<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'realogy' => [
        'geocoder_api_key' => env('REALOGY_GEOCODER_API_KEY'),
        'client_id' => env('REALOGY_CLIENT_ID'),
        'client_secret' => env('REALOGY_CLIENT_SECRET'),
        'api_key' => env('REALOGY_API_KEY'),
    ],

    'realogy_c21' => [
        'geocoder_api_key' => env('REALOGY_C21_GEOCODER_API_KEY'),
        'client_id' => env('REALOGY_C21_CLIENT_ID'),
        'client_secret' => env('REALOGY_C21_CLIENT_SECRET'),
        'api_key' => env('REALOGY_C21_API_KEY'),
    ],

    'realogy_cb' => [
        'geocoder_api_key' => env('REALOGY_CB_GEOCODER_API_KEY'),
        'client_id' => env('REALOGY_CB_CLIENT_ID'),
        'client_secret' => env('REALOGY_CB_CLIENT_SECRET'),
        'api_key' => env('REALOGY_CB_API_KEY'),
    ],

    'realogy_cor' => [
        'geocoder_api_key' => env('REALOGY_COR_GEOCODER_API_KEY'),
        'client_id' => env('REALOGY_COR_CLIENT_ID'),
        'client_secret' => env('REALOGY_COR_CLIENT_SECRET'),
        'api_key' => env('REALOGY_COR_API_KEY'),
    ],


    'zezoom' => [
        'here_auth_1' => env('ZEZOOM_HERE_AUTH_1', ''),
        'here_auth_2' => env('ZEZOOM_HERE_AUTH_2', ''),
        'google_maps_api_key' => env('ZEZOOM_GOOGLE_MAPS_API_KEY', ''),
    ],

];
