<?php

return [
    'enabled'       => env('GOOGLE_DRIVE_ENABLED', false),
    'client_id'     => env('GOOGLE_DRIVE_CLIENT_ID', ''),
    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET', ''),
    'redirect_uri'  => env('GOOGLE_DRIVE_REDIRECT_URI', ''),
];
