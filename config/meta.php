<?php

return [
    // Validated flow: facebook_login + Instagram Graph API via Facebook Pages
    'auth_mode' => env('META_AUTH_MODE', 'facebook_login'),

    'app_id'        => env('META_APP_ID'),
    'app_secret'    => env('META_APP_SECRET'),
    'redirect_uri'  => env('META_REDIRECT_URI', 'https://ia.lymity.com.br/admin/social/instagram/callback'),
    'graph_version' => env('META_GRAPH_VERSION', 'v25.0'),

    // Validated scopes for Facebook Login + Instagram Graph API flow
    'facebook_scopes' => array_filter(array_map('trim', explode(',', env(
        'META_FACEBOOK_SCOPES',
        'pages_show_list,pages_read_engagement,business_management,instagram_basic,instagram_content_publish'
    )))),

    // Alternative scopes for instagram_business_login (not the current primary flow)
    'instagram_scopes' => array_filter(array_map('trim', explode(',', env(
        'META_INSTAGRAM_SCOPES',
        ''
    )))),

    'instagram_publishing_enabled' => filter_var(env('INSTAGRAM_PUBLISHING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    // Official channel constants (validated manually)
    'official' => [
        'username'   => env('INSTAGRAM_OFFICIAL_USERNAME', 'lymity.ia'),
        'page_id'    => env('INSTAGRAM_OFFICIAL_PAGE_ID', '1069242536283477'),
        'ig_user_id' => env('INSTAGRAM_OFFICIAL_IG_USER_ID', '17841434234661171'),
    ],
];
