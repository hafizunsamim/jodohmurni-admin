<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics 4 (Reporting)
    |--------------------------------------------------------------------------
    |
    | Paparan ringkasan event GA4 untuk dashboard admin.
    |
    | Setup:
    | - GA4_PROPERTY_ID: ID property GA4 (nombor), contoh: 123456789
    | - GA4_CREDENTIALS_PATH: path fail JSON service account (disyorkan simpan luar repo)
    |
    | Nota:
    | - Service account mesti diberi akses "Viewer" dalam GA4 property.
    | - Event name yang dipaparkan selari dengan event yang dihantar dari client.
    |
    */

    'ga4' => [
        'property_id' => env('GA4_PROPERTY_ID', ''),
        // Pilih salah satu kaedah auth:
        // (A) OAuth user (recommended jika tiada Google Workspace)
        // (B) Service account JSON (jika property boleh add user tersebut)
        'credentials_path' => env('GA4_CREDENTIALS_PATH', ''),
        'oauth' => [
            'client_id' => env('GA4_OAUTH_CLIENT_ID', ''),
            'client_secret' => env('GA4_OAUTH_CLIENT_SECRET', ''),
            'refresh_token' => env('GA4_OAUTH_REFRESH_TOKEN', ''),
        ],

        // Default range: 7 hari terakhir (termasuk hari ini)
        'default_start_date' => env('GA4_DEFAULT_START_DATE', '7daysAgo'),
        'default_end_date' => env('GA4_DEFAULT_END_DATE', 'today'),

        // Event yang ingin dipaparkan di dashboard admin
        'event_names' => [
            'jm_homepage_visit',
            'sign_up',
            'login',
            'subscription_package_click',
            'purchase',
            'view_profile',
            'like_profile',
            'match_success',
        ],
    ],
];

