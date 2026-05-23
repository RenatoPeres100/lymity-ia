<?php

namespace App\Services\Seo;

use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\User;
use App\Services\Ai\AiTaskService;

class SeoAiService
{
    public function __construct(private AiTaskService $taskService) {}

    public function createAndRunTask(string $taskType, string $title, ?string $description, ?int $clientId, User $user, array $metadata = []): AiTask
    {
        $employee = AiEmployee::where('slug', 'seo-ia')
            ->where('status', 'active')
            ->first();

        if (!$employee) {
            $employee = AiEmployee::where('status', 'active')->first();
        }

        if (!$employee) {
            throw new \RuntimeException('Nenhum funcionário IA ativo disponível para tarefas SEO.');
        }

        $task = AiTask::create([
            'ai_employee_id'    => $employee->id,
            'client_id'         => $clientId,
            'created_by'        => $user->id,
            'title'             => $title,
            'description'       => $description,
            'task_type'         => $taskType,
            'priority'          => 'medium',
            'requires_approval' => true,
            'sensitive_action'  => false,
            'status'            => 'queued',
            'metadata'          => array_merge($metadata, ['client_id' => $clientId]),
        ]);

        return $this->taskService->runTask($task);
    }
}
