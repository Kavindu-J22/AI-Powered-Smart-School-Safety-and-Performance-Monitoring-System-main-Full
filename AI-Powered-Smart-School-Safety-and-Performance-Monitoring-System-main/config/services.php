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

    /*
    |--------------------------------------------------------------------------
    | AI Homework Management Service
    |--------------------------------------------------------------------------
    |
    | Configuration for the AI-Powered Homework Management Flask API
    |
    */
    'homework_ai' => [
        'base_url' => env('HOMEWORK_AI_BASE_URL', 'http://localhost:5001'),
        'timeout' => env('HOMEWORK_AI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audio Threat Detection Service
    |--------------------------------------------------------------------------
    |
    | Configuration for the Audio-Based Threat Detection Flask API
    |
    */
    'audio_threat' => [
        'url' => env('AUDIO_THREAT_API_URL', 'http://127.0.0.1:5002'),
        'timeout' => env('AUDIO_THREAT_TIMEOUT', 30),
    ],

];
