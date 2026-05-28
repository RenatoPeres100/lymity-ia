<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\DTO\AITextResponse;
use App\Services\AI\DTO\TokenEstimate;

interface AIProviderInterface
{
    public function generateText(array $payload): AITextResponse;

    public function countTokens(array $payload): TokenEstimate;

    public function isConfigured(): bool;

    public function providerName(): string;
}
