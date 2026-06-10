<?php

namespace App\Services\AI\DTO;

class AITextResponse
{
    public function __construct(
        public readonly bool    $success,
        public readonly string  $provider,
        public readonly string  $model,
        public readonly bool    $fallback_used = false,
        public readonly ?string $text = null,
        public readonly ?array  $json = null,
        public readonly ?array  $raw_response = null,
        public readonly ?int    $input_tokens = null,
        public readonly ?int    $output_tokens = null,
        public readonly ?int    $total_tokens = null,
        public readonly ?float  $estimated_cost_usd = null,
        public readonly ?string $error_message = null,
        public readonly ?string $finish_reason = null,
    ) {}

    public static function success(
        string  $provider,
        string  $model,
        ?string $text = null,
        ?array  $json = null,
        ?array  $raw = null,
        ?int    $inputTokens = null,
        ?int    $outputTokens = null,
        ?float  $cost = null,
        bool    $fallback = false,
        ?string $finishReason = null,
    ): self {
        return new self(
            success: true,
            provider: $provider,
            model: $model,
            fallback_used: $fallback,
            text: $text,
            json: $json,
            raw_response: $raw,
            input_tokens: $inputTokens,
            output_tokens: $outputTokens,
            total_tokens: ($inputTokens ?? 0) + ($outputTokens ?? 0),
            estimated_cost_usd: $cost,
            finish_reason: $finishReason,
        );
    }

    public static function error(string $provider, string $model, string $message): self
    {
        return new self(
            success: false,
            provider: $provider,
            model: $model,
            error_message: $message,
        );
    }
}
