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
    | Member / public app (helpdesk diisi oleh pengguna di sini).
    |
    | Lampiran disimpan pada stor app ahli (bukan admin). Jika admin dan ahli domain berbeza,
    | WAJIB set MEMBER_APP_URL atau HELPDESK_ATTACHMENT_BASE_URL ke URL akar app ahli
    | (contoh https://jodohmurni.com), jika tidak pautan /storage/... akan guna domain admin → 404.
    */
    'member_app' => [
        'url' => rtrim((string) env('MEMBER_APP_URL', env('APP_URL', 'http://localhost')), '/'),
        'attachment_base_url' => rtrim((string) env(
            'HELPDESK_ATTACHMENT_BASE_URL',
            env('MEMBER_APP_URL', env('APP_URL', 'http://localhost'))
        ), '/'),
    ],

];
