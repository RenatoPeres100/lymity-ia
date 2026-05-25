<?php

namespace App\Services\Ai;

use App\Models\AiTask;

class AiProviderManager
{
    /**
     * Resolve the configured provider instance.
     */
    public function provider(): AiProviderInterface
    {
        $name = strtolower(config('ai.provider', 'mock'));

        return match ($name) {
            'openai' => new OpenAiProvider(),
            'claude' => new ClaudeProvider(),
            default  => new MockAiProvider(),
        };
    }

    /**
     * Generate via the resolved provider using a structured payload.
     * Returns a standardised result array.
     */
    public function generateFromPayload(array $payload): array
    {
        return $this->provider()->generate($payload);
    }

    /**
     * Legacy method — kept for backward compatibility.
     * Callers that still pass an AiTask directly continue to work.
     *
     * @deprecated Prefer AiGenerationService->generateForTask()
     */
    public function generate(AiTask $task): string
    {
        $mock = new MockAiProvider();
        $result = $mock->generate($task);
        return $result['response'] ?? '';
    }
}
