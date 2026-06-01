<?php

return [
    'official_instagram' => [
        'username'   => env('INSTAGRAM_OFFICIAL_USERNAME', 'lymity.ia'),
        'page_id'    => env('INSTAGRAM_OFFICIAL_PAGE_ID', '1069242536283477'),
        'ig_user_id' => env('INSTAGRAM_OFFICIAL_IG_USER_ID', '17841434234661171'),
    ],

    'image' => [
        'disk'               => env('SOCIAL_IMAGE_DISK', 'public'),
        'path'               => env('SOCIAL_IMAGE_PATH', 'social/generated'),
        'public_base_url'    => env('SOCIAL_IMAGE_PUBLIC_BASE_URL', env('APP_URL', 'https://ia.lymity.com.br') . '/storage'),
        'width'              => (int) env('SOCIAL_IMAGE_DEFAULT_WIDTH', 1080),
        'height'             => (int) env('SOCIAL_IMAGE_DEFAULT_HEIGHT', 1080),
        'min_width'          => 320,
        'min_height'         => 320,
        'recommended_width'  => 1080,
        'recommended_height' => 1080,
        'allowed_mimes'      => ['image/jpeg', 'image/png'],
        'max_size_mb'        => (int) env('SOCIAL_IMAGE_MAX_SIZE_MB', 8),
    ],

    'carousel' => [
        'enabled'    => filter_var(env('AI_SOCIAL_CAROUSEL_GENERATION_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'min_slides' => (int) env('SOCIAL_CAROUSEL_MIN_SLIDES', 3),
        'max_slides' => (int) env('SOCIAL_CAROUSEL_MAX_SLIDES', 5),
    ],
];
