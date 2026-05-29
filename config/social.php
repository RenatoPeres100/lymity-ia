<?php

return [
    'official_instagram' => [
        'username'   => env('INSTAGRAM_OFFICIAL_USERNAME', 'lymity.ia'),
        'page_id'    => env('INSTAGRAM_OFFICIAL_PAGE_ID'),
        'ig_user_id' => env('INSTAGRAM_OFFICIAL_IG_USER_ID'),
    ],

    'image' => [
        'disk'               => env('SOCIAL_IMAGE_DISK', 'public'),
        'path'               => env('SOCIAL_IMAGE_PATH', 'social/generated'),
        'public_base_url'    => env('SOCIAL_IMAGE_PUBLIC_BASE_URL', env('APP_URL', 'https://ia.lymity.com.br') . '/storage'),
        'min_width'          => 320,
        'min_height'         => 320,
        'recommended_width'  => 1080,
        'recommended_height' => 1080,
        'allowed_mimes'      => ['image/jpeg', 'image/png'],
        'max_size_mb'        => 8,
    ],
];
