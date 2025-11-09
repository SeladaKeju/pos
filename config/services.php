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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'xendit' => [
        'secret_key'     => env('xnd_development_4i8RpzsT9GMq1O7pLXFKTOeYSwGXXemOH95NzKRMlHW0retLm4XgZH151AnWd2N'),
        'callback_token' => env('xnd_public_development_QYn2sPbN8t6lJgmfDPXrkjFruyX1k4hufcnDcRdY3FxaCgguKUeF_CjIMbrfjjad'),
        'api_base'       => 'https://api.xendit.co',
    ],
];
