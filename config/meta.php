<?php

return [
    'app_id'     => env('META_APP_ID'),
    'app_secret' => env('META_APP_SECRET'),
    'redirect_uri' => env('META_REDIRECT_URI', 'https://ia.lymity.com.br/admin/social/instagram/callback'),
    'graph_version' => env('META_GRAPH_VERSION', 'v25.0'),
    'instagram_publishing_enabled' => filter_var(env('INSTAGRAM_PUBLISHING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'scopes' => [
        'pages_show_list',
        'pages_read_engagement',
        'instagram_basic',
        'instagram_content_publish',
    ],
];
