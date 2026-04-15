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

    // ─── AI Services ─────────────────────────────────────────────────
    'ai_provider' => env('AI_PROVIDER', 'openai'), // openai | gemini | claude

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model'   => env('OPENAI_MODEL', 'gpt-4o'),
        'image_model' => 'dall-e-3',
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-1.5-pro'),
        'image_model' => 'imagen-3.0-generate-002',
    ],

    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model'   => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
    ],

    'tavily' => [
        'api_key' => env('TAVILY_API_KEY'),
    ],

    // ─── Social Media ─────────────────────────────────────────────────
    'facebook' => [
        'page_id'      => env('FACEBOOK_PAGE_ID'),
        'access_token' => env('FACEBOOK_ACCESS_TOKEN'),
    ],

    'instagram' => [
        'account_id'   => env('INSTAGRAM_ACCOUNT_ID'),
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
    ],

    'linkedin' => [
        'access_token' => env('LINKEDIN_ACCESS_TOKEN'),
        'person_id'    => env('LINKEDIN_PERSON_ID'),
    ],

];
