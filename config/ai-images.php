<?php

return [
    'enabled' => filter_var(env('GEMINI_IMAGE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'provider' => env('AI_PROVIDER', 'google'),

    'gemini' => [
        'api_key_configured'       => ! empty(env('GEMINI_API_KEY')),
        'text_model'               => env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash'),
        'image_model'              => env('GEMINI_IMAGE_MODEL', 'imagen-3.0-generate-002'),
        'timeout'                  => (int) env('GEMINI_IMAGE_TIMEOUT', 120),
        'daily_limit'              => env('GEMINI_IMAGE_DAILY_LIMIT'),
        'monthly_limit'            => env('GEMINI_IMAGE_MONTHLY_LIMIT'),
        'max_images_per_request'   => (int) env('GEMINI_IMAGE_MAX_IMAGES_PER_REQUEST', 5),
    ],

    'storage' => [
        'disk'            => env('AI_IMAGE_STORAGE_DISK', 'public'),
        'path'            => env('AI_IMAGE_STORAGE_PATH', 'ai-images'),
        'public_base_url' => env('AI_IMAGE_PUBLIC_BASE_URL', env('APP_URL', 'https://ia.lymity.com.br') . '/storage'),
    ],

    'image' => [
        'width'         => (int) env('AI_IMAGE_DEFAULT_WIDTH', 1080),
        'height'        => (int) env('AI_IMAGE_DEFAULT_HEIGHT', 1080),
        'max_size_mb'   => (int) env('AI_IMAGE_MAX_SIZE_MB', 8),
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
