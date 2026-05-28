<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\GoogleGeminiProvider;
use RuntimeException;

class AIProviderManager
{
    public function provider(): AIProviderInterface
    {
        $name = strtolower(config('ai.provider', 'mock'));

        return match ($name) {
            'google' => new GoogleGeminiProvider(),
            default  => throw new RuntimeException(
                "Provider '{$name}' não suporta geração de texto estruturado. Configure AI_PROVIDER=google."
            ),
        };
    }

    public function isGeminiConfigured(): bool
    {
        return config('ai.provider') === 'google' && !empty(config('ai.gemini_api_key'));
    }
}
