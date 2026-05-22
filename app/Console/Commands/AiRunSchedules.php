<?php

namespace App\Console\Commands;

use App\Services\Ai\AiWorkSchedulerService;
use Illuminate\Console\Command;

class AiRunSchedules extends Command
{
    protected $signature = 'ai:run-schedules';
    protected $description = 'Processa todos os agendamentos de funcionários IA com execução pendente';

    public function handle(AiWorkSchedulerService $scheduler): int
    {
        $this->info('Verificando agendamentos pendentes...');

        $count = $scheduler->runDueSchedules();

        $this->info("Agendamentos processados: {$count}");

        return Command::SUCCESS;
    }
}
