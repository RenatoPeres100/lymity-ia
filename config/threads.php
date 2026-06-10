<?php

return [
    'app_id'             => env('THREADS_APP_ID'),
    'app_secret'         => env('THREADS_APP_SECRET'),
    'redirect_uri'       => env('THREADS_REDIRECT_URI'),
    'graph_version'      => env('THREADS_GRAPH_VERSION', 'v1.0'),
    'publishing_enabled' => filter_var(env('THREADS_PUBLISHING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'base_url'           => env('THREADS_BASE_URL', 'https://graph.threads.net'),
    'scopes'             => array_filter(array_map('trim', explode(',', env('THREADS_SCOPES', 'threads_basic,threads_content_publish')))),
];
