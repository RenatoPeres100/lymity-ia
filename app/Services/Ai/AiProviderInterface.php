<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    /**
     * Generate a response for the given payload.
     *
     * Payload keys:
     *   task_type, title, description, client_name, client_context,
     *   employee (AiEmployee|null), system_prompt, user_message,
     *   memories, feedbacks
     *
     * Returns a standardised result array — see AiGenerationService::emptyResult().
     */
    public function generate(array $payload): array;

    /**
     * Verify that the provider is reachable and credentials are valid.
     * Returns ['success' => bool, 'message' => string, 'model' => string|null, 'latency_ms' => int|null].
     */
    public function testConnection(): array;

    /** Whether the provider reliably returns JSON-structured output. */
    public function supportsStructuredOutput(): bool;

    /** Human-readable provider name (e.g. 'mock', 'openai', 'claude'). */
    public function providerName(): string;
}
