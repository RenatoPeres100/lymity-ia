<?php

namespace App\Console\Commands;

use App\Models\AiTask;
use App\Services\Ai\AiTaskService;
use Illuminate\Console\Command;

class AiRunTask extends Command
{
    protected $signature = 'ai:run-task {task_id : ID da tarefa a executar}';
    protected $description = 'Executa uma tarefa de funcionário IA pelo ID';

    public function handle(AiTaskService $service): int
    {
        $taskId = (int) $this->argument('task_id');
        $task   = AiTask::find($taskId);

        if (!$task) {
            $this->error("Tarefa #{$taskId} não encontrada.");
            return Command::FAILURE;
        }

        if (!$task->canRun()) {
            $this->warn("Tarefa #{$taskId} não pode ser executada. Status atual: {$task->status}");
            return Command::FAILURE;
        }

        $this->info("Executando tarefa #{$taskId}: {$task->title}");

        try {
            $result = $service->runTask($task);
            $this->info("Status final: {$result->status}");

            if ($result->output) {
                $this->line('--- Output (primeiros 500 chars) ---');
                $this->line(substr($result->output, 0, 500));
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
