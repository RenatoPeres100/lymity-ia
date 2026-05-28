<?php

namespace App\Services\AI;

use App\Models\AiEmployee;
use App\Models\AiUsageRecord;
use App\Services\AI\DTO\AITextResponse;
use App\Services\AI\DTO\TokenEstimate;
use RuntimeException;

class AICostGuardService
{
    public function assertCanGenerate(string $taskType, ?AiEmployee $agent = null): void
    {
        if (!$this->isProviderConfigured()) {
            throw new RuntimeException(
                'Gemini não configurado. Verifique GEMINI_API_KEY e AI_PROVIDER=google no .env.'
            );
        }

        $dailyLimit = config('ai.daily_request_limit', 20);
        if ($dailyLimit > 0 && $this->getDailyRequestCount() >= $dailyLimit) {
            throw new RuntimeException(
                "Limite de uso de IA atingido. Revise AI_DAILY_REQUEST_LIMIT ou AI_MONTHLY_BUDGET_USD."
            );
        }

        $monthlyBudget = config('ai.monthly_budget_usd', 20.0);
        if ($monthlyBudget > 0 && $this->getMonthlyEstimatedCost() >= $monthlyBudget) {
            throw new RuntimeException(
                "Limite de uso de IA atingido. Revise AI_DAILY_REQUEST_LIMIT ou AI_MONTHLY_BUDGET_USD."
            );
        }
    }

    public function getDailyUsage(): array
    {
        $count = $this->getDailyRequestCount();
        $limit = config('ai.daily_request_limit', 20);
        return [
            'count'     => $count,
            'limit'     => $limit,
            'remaining' => max(0, $limit - $count),
            'exceeded'  => $count >= $limit,
        ];
    }

    public function getMonthlyUsage(): array
    {
        $cost   = $this->getMonthlyEstimatedCost();
        $budget = config('ai.monthly_budget_usd', 20.0);
        return [
            'estimated_cost_usd' => round($cost, 4),
            'budget_usd'         => $budget,
            'remaining_usd'      => max(0, $budget - $cost),
            'exceeded'           => $cost >= $budget,
        ];
    }

    public function estimateCost(TokenEstimate $estimate): float
    {
        return $estimate->estimated_cost_usd ?? 0.0;
    }

    public function registerUsage(AITextResponse $response, array $context = []): AiUsageRecord
    {
        return AiUsageRecord::create([
            'provider'           => $response->provider,
            'model'              => $response->model,
            'agent_id'           => $context['agent_id'] ?? null,
            'task_type'          => $context['task_type'] ?? null,
            'input_tokens'       => $response->input_tokens,
            'output_tokens'      => $response->output_tokens,
            'estimated_cost_usd' => $response->estimated_cost_usd,
            'related_type'       => $context['related_type'] ?? null,
            'related_id'         => $context['related_id'] ?? null,
            'created_at'         => now(),
        ]);
    }

    private function getDailyRequestCount(): int
    {
        return AiUsageRecord::whereDate('created_at', today())
            ->where('provider', '!=', 'mock')
            ->count();
    }

    private function getMonthlyEstimatedCost(): float
    {
        return (float) AiUsageRecord::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('provider', '!=', 'mock')
            ->sum('estimated_cost_usd');
    }

    private function isProviderConfigured(): bool
    {
        return config('ai.provider') === 'google' && !empty(config('ai.gemini_api_key'));
    }
}
