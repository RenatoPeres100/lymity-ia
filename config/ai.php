<?php

return [
    'provider'                  => env('AI_PROVIDER', 'mock'),
    'api_key'                   => env('AI_API_KEY'),
    'model'                     => env('AI_MODEL', 'mock-growth-agent'),
    'mock_enabled'              => env('AI_PROVIDER', 'mock') === 'mock',
    'default_requires_approval' => true,
    'max_tasks_per_run'         => 3,
];
