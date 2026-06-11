<?php

$useMetaApp = filter_var(env('THREADS_USE_META_APP', false), FILTER_VALIDATE_BOOLEAN);

return [
    'use_meta_app' => $useMetaApp,

    // App credentials: use Meta App or dedicated Threads App
    'app_id'     => $useMetaApp ? env('META_APP_ID') : env('THREADS_APP_ID'),
    'app_secret' => $useMetaApp ? env('META_APP_SECRET') : env('THREADS_APP_SECRET'),

    // Always use Threads-specific redirect URI
    'redirect_uri' => env('THREADS_REDIRECT_URI', env('APP_URL', 'https://ia.lymity.com.br') . '/admin/social/threads/callback'),

    'graph_version'    => env('THREADS_GRAPH_VERSION', 'v1.0'),
    'base_url'         => env('THREADS_BASE_URL', 'https://graph.threads.net'),
    'oauth_base_url'   => env('THREADS_OAUTH_BASE_URL', 'https://threads.net/oauth/authorize'),
    'token_url'        => env('THREADS_TOKEN_URL', 'https://graph.threads.net/oauth/access_token'),

    'publishing_enabled' => filter_var(env('THREADS_PUBLISHING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'scopes' => array_filter(array_map('trim', explode(',', env('THREADS_SCOPES', 'threads_basic,threads_content_publish')))),

    // Raw env values for diagnostics (never expose secrets)
    '_raw' => [
        'use_meta_app'        => $useMetaApp,
        'threads_app_id_set'  => !empty(env('THREADS_APP_ID')),
        'threads_secret_set'  => !empty(env('THREADS_APP_SECRET')),
        'meta_app_id_set'     => !empty(env('META_APP_ID')),
        'meta_secret_set'     => !empty(env('META_APP_SECRET')),
        'redirect_uri_set'    => !empty(env('THREADS_REDIRECT_URI')),
    ],
];
