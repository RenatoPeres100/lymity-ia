<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TestQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function handle(): void
    {
        $timestamp = now()->toDateTimeString();

        Log::info('[TestQueueJob] Processado com sucesso', ['timestamp' => $timestamp]);

        Cache::put('lymity_queue_test_last_run', $timestamp, now()->addDay());

        try {
            ActivityLog::create([
                'action'      => 'test_queue_job_processed',
                'level'       => 'info',
                'description' => 'TestQueueJob processado com sucesso pela fila',
                'metadata'    => json_encode(['timestamp' => $timestamp, 'queue' => $this->queue ?? 'default']),
            ]);
        } catch (\Throwable) {
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[TestQueueJob] Falhou', ['error' => $e->getMessage()]);
    }
}
