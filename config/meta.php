<?php

return [
    'auth_mode' => env('META_AUTH_MODE', 'facebook_business_login'),

    'app_id'      => env('META_APP_ID'),
    'app_secret'  => env('META_APP_SECRET'),
    'redirect_uri'  => env('META_REDIRECT_URI', 'https://ia.lymity.com.br/admin/social/instagram/callback'),
    'graph_version' => env('META_GRAPH_VERSION', 'v25.0'),

    'facebook_scopes' => array_filter(array_map('trim', explode(',', env(
        'META_FACEBOOK_SCOPES',
        'instagram_business_basic,instagram_business_content_publish'
    )))),

    'instagram_scopes' => array_filter(array_map('trim', explode(',', env(
        'META_INSTAGRAM_SCOPES',
        'instagram_business_basic,instagram_business_content_publish'
    )))),

    'instagram_publishing_enabled' => filter_var(env('INSTAGRAM_PUBLISHING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    // Legacy alias — kept for backwards-compat; reads same env var as facebook_scopes
    'scopes' => array_filter(array_map('trim', explode(',', env(
        'META_FACEBOOK_SCOPES',
        'instagram_business_basic,instagram_business_content_publish'
    )))),
];
