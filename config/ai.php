<?php

return [
    // Provider: mock | openai | claude
    'provider' => env('AI_PROVIDER', 'mock'),

    // Safety gate — set true to allow real provider API calls
    'real_enabled' => env('AI_REAL_ENABLED', false),

    // API key — never expose in views, logs or dumps
    'api_key' => env('AI_API_KEY', ''),

    // Model identifier (e.g. gpt-4o-mini, claude-haiku-4-5-20251001, mock-growth-agent)
    'model' => env('AI_MODEL', 'mock-growth-agent'),

    // Generation parameters
    'max_tokens'  => (int) env('AI_MAX_TOKENS', 1200),
    'temperature' => (float) env('AI_TEMPERATURE', 0.7),

    // Cost & usage limits
    'monthly_budget_limit' => (float) env('AI_MONTHLY_BUDGET_LIMIT', 100),
    'daily_task_limit'     => (int)   env('AI_DAILY_TASK_LIMIT', 50),

    // If true, fall back to mock when real provider fails (default: false — surface errors)
    'fallback_to_mock_on_error' => env('AI_FALLBACK_TO_MOCK', false),

    // Legacy compatibility
    'mock_enabled'              => env('AI_PROVIDER', 'mock') === 'mock',
    'default_requires_approval' => true,
    'max_tasks_per_run'         => 3,

    // Approximate cost estimates (USD per 1K tokens) — informational only
    'cost_estimates' => [
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
