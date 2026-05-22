<?php

namespace App\Services\Ai;

use App\Models\AiTask;

class AiProviderManager
{
    public function generate(AiTask $task): string
    {
        return $this->resolve()->generate($task);
    }

    private function resolve(): MockAiProvider
    {
        $provider = config('ai.provider', 'mock');

        return match ($provider) {
            default => new MockAiProvider(),
        };
    }
}
