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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'razorpay' => [
        'url' => env('RAZORPAY_API_URL', 'https://api.razorpay.com'),
        'key' => env('RAZORPAY_KEY_ID'),
        'secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'web' => [
            'apiKey' => env('FIREBASE_WEB_API_KEY'),
            'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
            'messagingSenderId' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
            'appId' => env('FIREBASE_WEB_APP_ID'),
            'measurementId' => env('FIREBASE_WEB_MEASUREMENT_ID'),
            'vapidKey' => env('FIREBASE_WEB_VAPID_KEY'),
        ],
    ],

];
