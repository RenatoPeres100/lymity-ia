<?php

namespace App\Services\AI\DTO;

class TokenEstimate
{
    public function __construct(
        public readonly string  $provider,
        public readonly string  $model,
        public readonly ?int    $input_tokens = null,
        public readonly ?int    $estimated_output_tokens = null,
        public readonly ?int    $estimated_total_tokens = null,
        public readonly ?float  $estimated_cost_usd = null,
    ) {}
}
