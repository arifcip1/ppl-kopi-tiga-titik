<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouteService (ORS) - Routing Rute Jalan OSM (PPL-26 / F7)
    |--------------------------------------------------------------------------
    */
    'openrouteservice' => [
        'api_key'  => env('OPENROUTESERVICE_API_KEY'),
        'base_url' => env('OPENROUTESERVICE_BASE_URL', 'https://api.openrouteservice.org'),
        'timeout'  => (int) env('OPENROUTESERVICE_TIMEOUT', 5),
        'profile'  => env('OPENROUTESERVICE_PROFILE', 'driving-car'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway - QRIS Sandbox (PPL-27 / F6)
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-default-test-key'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-default-test-key'),
        'merchant_id'   => env('MIDTRANS_MERCHANT_ID', 'G123456789'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'  => true,
        'is_3ds'        => true,
    ],

];
