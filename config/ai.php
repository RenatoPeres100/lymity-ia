<?php

return [
    // Provider: mock | openai | claude | google
    'provider' => env('AI_PROVIDER', 'mock'),

    // === GEMINI (Google) ===
    'gemini_api_key'             => env('GEMINI_API_KEY'),
    'gemini_text_model'          => env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash'),
    'gemini_text_fallback_model' => env('GEMINI_TEXT_FALLBACK_MODEL', 'gemini-2.5-flash-lite'),

    // === FEATURE TOGGLES ===
    'enable_grounding'     => filter_var(env('AI_ENABLE_GROUNDING', false), FILTER_VALIDATE_BOOLEAN),
    'enable_context_cache' => filter_var(env('AI_ENABLE_CONTEXT_CACHE', false), FILTER_VALIDATE_BOOLEAN),
    'require_approval'     => filter_var(env('AI_REQUIRE_APPROVAL', true), FILTER_VALIDATE_BOOLEAN),
    'text_only_mode'       => filter_var(env('AI_TEXT_ONLY_MODE', true), FILTER_VALIDATE_BOOLEAN),

    // === COST & LIMITS ===
    'daily_request_limit' => (int)   env('AI_DAILY_REQUEST_LIMIT', 20),
    'monthly_budget_usd'  => (float) env('AI_MONTHLY_BUDGET_USD', 20.0),
    'max_output_tokens'   => (int)   env('AI_MAX_OUTPUT_TOKENS', 3500),
    'temperature'         => (float) env('AI_TEMPERATURE', 0.7),

    // === LEGACY COMPAT ===
    'real_enabled'              => env('AI_REAL_ENABLED', false),
    'api_key'                   => env('AI_API_KEY', ''),
    'model'                     => env('AI_MODEL', 'mock-growth-agent'),
    'max_tokens'                => (int)   env('AI_MAX_TOKENS', 1200),
    'monthly_budget_limit'      => (float) env('AI_MONTHLY_BUDGET_LIMIT', 100),
    'daily_task_limit'          => (int)   env('AI_DAILY_TASK_LIMIT', 50),
    'fallback_to_mock_on_error' => env('AI_FALLBACK_TO_MOCK', false),
    'mock_enabled'              => env('AI_PROVIDER', 'mock') === 'mock',
    'default_requires_approval' => true,
    'max_tasks_per_run'         => 3,

    // === COST ESTIMATES (USD per 1K tokens) — informational only ===
    'cost_estimates' => [
        'google' => [
            'gemini-2.5-flash'      => ['input' => 0.000075, 'output' => 0.0003],
            'gemini-2.5-flash-lite' => ['input' => 0.0000375, 'output' => 0.00015],
            'gemini-1.5-flash'      => ['input' => 0.000075,  'output' => 0.0003],
        ],
        'openai' => [
            'gpt-4o-mini' => ['input' => 0.00015, 'output' => 0.0006],
            'gpt-4o'      => ['input' => 0.005,   'output' => 0.015],
            'gpt-3.5'     => ['input' => 0.001,   'output' => 0.002],
        ],
        'claude' => [
            'claude-haiku'  => ['input' => 0.00025, 'output' => 0.00125],
            'claude-sonnet' => ['input' => 0.003,   'output' => 0.015],
            'claude-opus'   => ['input' => 0.015,   'output' => 0.075],
        ],
        'mock' => [
            'mock-growth-agent' => ['input' => 0.0, 'output' => 0.0],
        ],
    ],
];
