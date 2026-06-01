<?php

namespace App\Services\Agents;

use App\Models\ActivityLog;
use App\Models\AgentRoutine;
use App\Models\AgentRoutineRun;
use App\Services\AI\AIProviderManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgentRoutineExecutionService
{
    public function __construct(
        private AIProviderManager      $providerManager,
        private RoutineHandlerResolver $resolver,
    ) {}

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Run all due routines. Returns summary array.
     */
    public function runDueRoutines(?Carbon $now = null, bool $dryRun = false, ?int $onlyRoutineId = null): array
    {
        $now  = $now ?? now();
        $runs = [];

        $routines = $this->getDueRoutines($now);

        if ($onlyRoutineId) {
            $routines = $routines->filter(fn($r) => $r->id === $onlyRoutineId)->values();
        }

        foreach ($routines as $routine) {
            $run = $this->runRoutine($routine, $now, $dryRun);
            $runs[] = [
                'routine_id'   => $routine->id,
                'routine_title'=> $routine->title,
                'run_id'       => $run?->id,
                'status'       => $run?->status ?? 'skipped',
                'items'        => $run?->items_created ?? 0,
                'approvals'    => $run?->approvals_created ?? 0,
                'error'        => $run?->error_message,
            ];
        }

        return $runs;
    }

    public function getDueRoutines(?Carbon $now = null): Collection
    {
        $now = $now ?? now();
        return AgentRoutine::where('active', true)
            ->whereNotIn('status', ['paused', 'disabled'])
            ->where(function ($q) use ($now) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })
            ->with('aiEmployee', 'company')
            ->get();
    }

    /**
     * Run a single routine. Returns the RoutineRun record.
     */
    public function runRoutine(AgentRoutine $routine, ?Carbon $now = null, bool $dryRun = false): AgentRoutineRun
    {
        $now       = $now ?? now();
        $runKey    = $this->createRunKey($routine, $now);
        $quantity  = $routine->getEffectiveQuantity();

        // Duplicate guard
        $existing = AgentRoutineRun::where('run_key', $runKey)->first();
        if ($existing && !$dryRun) {
            $this->log($routine, 'info', "agent_routine_run_skipped_duplicate", ['run_key' => $runKey]);
            Log::info("[AgentRoutineEngine] Skipping duplicate run_key={$runKey}");
            return $existing;
        }

        $run = AgentRoutineRun::create([
            'agent_routine_id' => $routine->id,
            'run_key'          => $dryRun ? null : $runKey,
            'routine_type'     => $routine->routine_type,
            'ai_employee_id'   => $routine->ai_employee_id,
            'company_id'       => $routine->company_id,
            'client_id'        => $routine->client_id,
            'scheduled_for'    => $now,
            'status'           => 'running',
            'started_at'       => now(),
            'items_requested'  => $quantity,
        ]);

        $this->log($routine, 'info', 'agent_routine_run_started', [
            'run_id' => $run->id, 'quantity' => $quantity, 'dry_run' => $dryRun,
        ]);

        try {
            $this->assertProviderConfigured();

            $handler = $this->resolver->resolve($routine);
            $result  = $handler->handle($routine, $run, $quantity, $dryRun);

            $run->update([
                'status'         => $dryRun ? 'skipped' : 'completed',
                'finished_at'    => now(),
                'items_created'  => $result['itemsCreated'],
                'approvals_created' => $result['approvalsCreated'],
                'output_summary' => implode("\n", $result['summaries'] ?? []),
            ]);

            // Update routine timestamps
            if (!$dryRun) {
                $nextRun = $this->calculateNextRunAt($routine, $now);
                $routine->update([
                    'last_run_at' => now(),
                    'next_run_at' => $nextRun,
                    'last_error'  => null,
                ]);
                $this->log($routine, 'info', 'agent_routine_run_completed', [
                    'run_id'       => $run->id,
                    'items'        => $result['itemsCreated'],
                    'approvals'    => $result['approvalsCreated'],
                    'next_run_at'  => $nextRun?->toIso8601String(),
                ]);
            }
        } catch (Throwable $e) {
            $safe = $this->redact($e->getMessage());
            Log::error("[AgentRoutineEngine] Routine #{$routine->id} failed: {$safe}");

            $run->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_message' => $safe,
            ]);

            $routine->update([
                'last_run_at' => now(),
                'next_run_at' => $this->calculateNextRunAt($routine, $now),
                'last_error'  => $safe,
            ]);

            $this->log($routine, 'error', 'agent_routine_run_failed', ['error' => $safe, 'run_id' => $run->id]);
        }

        return $run->fresh();
    }

    // ── Scheduling ─────────────────────────────────────────────────────────────

    public function calculateNextRunAt(AgentRoutine $routine, ?Carbon $from = null): ?Carbon
    {
        $from = $from ?? now();
        $time = $routine->time_of_day ?? '08:00';
        [$h, $m] = array_pad(explode(':', $time), 2, '0');

        $base = $from->copy()->setHour((int)$h)->setMinute((int)$m)->setSecond(0);

        if ($routine->frequency === 'daily') {
            $days = $routine->days_of_week ?? [];
            $next = $base->copy();
            if ($next->lte($from)) $next->addDay();
            if (!empty($days)) {
                for ($i = 0; $i < 7; $i++) {
                    if (in_array(strtolower($next->format('l')), $days)) break;
                    $next->addDay();
                }
            }
            return $next;
        }

        if ($routine->frequency === 'weekly') {
            $days = $routine->days_of_week ?? [];
            if (empty($days)) {
                return $base->copy()->addWeek();
            }
            $dayMap = [
                'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6,'sunday'=>0,
            ];
            $targets = array_filter(array_map(fn($d) => $dayMap[$d] ?? null, $days));
            $next    = $base->copy();
            if ($next->lte($from)) $next->addDay();
            for ($i = 0; $i <= 7; $i++) {
                if (in_array((int)$next->format('w'), $targets)) return $next;
                $next->addDay();
            }
            return $base->copy()->addWeek();
        }

        if ($routine->frequency === 'monthly') {
            return $base->copy()->addMonth();
        }

        return null;
    }

    public function createRunKey(AgentRoutine $routine, Carbon $scheduledFor): string
    {
        $slot = $scheduledFor->format('Y-m-d-H-i');
        return "routine:{$routine->id}:slot:{$slot}";
    }

    // ── Guards ─────────────────────────────────────────────────────────────────

    private function assertProviderConfigured(): void
    {
        $provider = $this->providerManager->provider();
        if (!$provider->isConfigured()) {
            throw new \RuntimeException(
                'Provider de IA não configurado. Verifique GEMINI_API_KEY e AI_PROVIDER=google no .env.'
            );
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function log(AgentRoutine $routine, string $level, string $action, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => null,
                'action'      => $action,
                'module'      => 'agent_routines',
                'level'       => $level,
                'description' => "Rotina #{$routine->id}: {$routine->title}",
                'metadata'    => array_merge([
                    'routine_id' => $routine->id,
                    'agent'      => $routine->aiEmployee?->name,
                ], $extra),
            ]);
        } catch (Throwable) {}
    }

    private function redact(string $msg): string
    {
        return preg_replace('/(?:key|token|secret)=[^\s&]+/i', '***', $msg);
    }
}
