<?php

namespace App\Console\Commands;

use App\Jobs\TestQueueJob;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class QueueTestCommand extends Command
{
    protected $signature   = 'queue:test';
    protected $description = 'Dispara um TestQueueJob para verificar se a fila está funcionando';

    public function handle(): int
    {
        $this->info('');
        $this->info('========================================');
        $this->info('  Lymity IA — Queue Test');
        $this->info('========================================');
        $this->info('');

        $conn = config('queue.default', 'sync');
        $this->info("  Queue connection: {$conn}");
        $this->info('');

        TestQueueJob::dispatch();

        $this->info('<fg=green>  [OK] TestQueueJob despachado com sucesso!</>');
        $this->info('');

        if ($conn !== 'sync') {
            $this->warn('  Para processar o job, execute em outro terminal:');
            $this->line('  php artisan queue:work --stop-when-empty');
        } else {
            $this->info('  Queue=sync: o job foi processado imediatamente (sem worker necessário).');
        }

        $this->info('');
        $this->info('========================================');
        $this->info('');

        try {
            ActivityLog::create([
                'action'      => 'queue_test_dispatched',
                'level'       => 'info',
                'description' => 'TestQueueJob despachado via command queue:test',
                'metadata'    => json_encode(['queue_connection' => $conn]),
            ]);
        } catch (\Throwable) {
        }

        return self::SUCCESS;
    }
}
