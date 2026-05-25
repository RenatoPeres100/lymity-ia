<?php

namespace App\Services\Ai;

use App\Models\ActivityLog;
use App\Models\AiProviderCall;
use App\Models\Client;

class AiCostService
{
    public function getDailyTaskCount(): int
    {
        return AiProviderCall::whereDate('created_at', today())
            ->where('provider', '!=', 'mock')
            ->count();
    }

    public function getMonthlyEstimatedCost(): float
    {
        return (float) AiProviderCall::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('provider', '!=', 'mock')
            ->sum('estimated_cost');
    }

    public function getTotalCallsToday(): int
    {
        return AiProviderCall::whereDate('created_at', today())->count();
    }

    public function getFailedCallsToday(): int
    {
        return AiProviderCall::whereDate('created_at', today())
            ->where('status', 'failed')
            ->count();
    }

    public function canRunTask(?Client $client = null): bool
    {
        // Always allow mock
        if (config('ai.provider', 'mock') === 'mock') {
            return true;
        }

        if (!config('ai.real_enabled', false)) {
            return false;
        }

        if ($this->isDailyLimitExceeded()) {
            return false;
        }

        if ($this->isMonthlyBudgetExceeded()) {
            return false;
        }

        return true;
    }

    public function checkLimitsOrFail(): void
    {
        if ($this->isDailyLimitExceeded()) {
            throw new \RuntimeException(
                "Limite diário de tarefas reais atingido ({$this->getDailyTaskCount()}/".config('ai.daily_task_limit', 50).'). '
                . 'Aguarde amanhã ou use AI_PROVIDER=mock.'
            );
        }

        if ($this->isMonthlyBudgetExceeded()) {
            $cost  = number_format($this->getMonthlyEstimatedCost(), 4);
            $limit = config('ai.monthly_budget_limit', 100);
            throw new \RuntimeException(
                "Limite de orçamento mensal atingido (US$ {$cost}/US$ {$limit}). "
                . 'Configure AI_MONTHLY_BUDGET_LIMIT no .env.'
            );
        }
    }

    public function isDailyLimitExceeded(): bool
    {
        $limit = (int) config('ai.daily_task_limit', 50);
        return $limit > 0 && $this->getDailyTaskCount() >= $limit;
    }

    public function isMonthlyBudgetExceeded(): bool
    {
        $limit = (float) config('ai.monthly_budget_limit', 100);
        return $limit > 0 && $this->getMonthlyEstimatedCost() >= $limit;
    }

    public function logLimitWarning(string $type): void
    {
        ActivityLog::create([
            'action'      => 'ai_limit_warning',
            'description' => "[AI Cost] Limite de {$type} atingido.",
            'level'       => 'warning',
            'metadata'    => [
                'type'         => $type,
                'daily_count'  => $this->getDailyTaskCount(),
                'monthly_cost' => $this->getMonthlyEstimatedCost(),
            ],
        ]);
    }

    public function getUsageSummary(): array
    {
        return [
            'daily_real_calls'       => $this->getDailyTaskCount(),
            'daily_limit'            => config('ai.daily_task_limit', 50),
            'monthly_cost_usd'       => round($this->getMonthlyEstimatedCost(), 6),
            'monthly_budget_usd'     => config('ai.monthly_budget_limit', 100),
            'daily_limit_exceeded'   => $this->isDailyLimitExceeded(),
            'monthly_budget_exceeded'=> $this->isMonthlyBudgetExceeded(),
            'total_calls_today'      => $this->getTotalCallsToday(),
            'failed_calls_today'     => $this->getFailedCallsToday(),
        ];
    }
}
