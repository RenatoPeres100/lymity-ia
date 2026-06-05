<?php

namespace App\Services\AI\Memory;

use App\Models\AgentTask;
use App\Models\AiEmployee;
use App\Models\AiMemory;
use App\Models\User;
use Illuminate\Support\Collection;

class AIMemoryService
{
    public function storeMemory(array $data): AiMemory
    {
        return AiMemory::create(array_merge([
            'active'          => true,
            'weight'          => 1,
            'relevance_score' => 50,
        ], $data));
    }

    public function storeFeedbackMemory(array $data): AiMemory
    {
        return $this->storeMemory(array_merge([
            'memory_type'     => 'feedback',
            'weight'          => 2,
            'relevance_score' => 70,
        ], $data));
    }

    public function getRelevantMemoriesForTask(
        AgentTask $task,
        AiEmployee $employee,
        int $limit = 5
    ): Collection {
        return AiMemory::active()
            ->where(function ($q) use ($task, $employee) {
                // Same task
                $q->where('agent_task_id', $task->id)
                  // Same employee
                  ->orWhere(function ($q2) use ($employee, $task) {
                      $q2->where('ai_employee_id', $employee->id)
                         ->whereNull('agent_task_id');
                  })
                  // Company-wide with no task/employee binding
                  ->orWhere(function ($q2) use ($task) {
                      if ($task->company_id) {
                          $q2->where('company_id', $task->company_id)
                             ->whereNull('ai_employee_id')
                             ->whereNull('agent_task_id');
                      }
                  });
            })
            // Strict scope isolation
            ->where(function ($q) use ($task) {
                $q->where('company_id', $task->company_id)
                  ->orWhereNull('company_id');
            })
            ->where(function ($q) use ($task) {
                if ($task->client_id) {
                    $q->where('client_id', $task->client_id)
                      ->orWhereNull('client_id');
                } else {
                    $q->whereNull('client_id');
                }
            })
            ->orderByDesc('relevance_score')
            ->orderByDesc('weight')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function compactMemoryIfNeeded(AiMemory $memory): AiMemory
    {
        if (!$memory->compact_content && $memory->content) {
            $compact = mb_substr($memory->content, 0, 300);
            if (mb_strlen($memory->content) > 300) {
                $compact .= '...';
            }
            $memory->update(['compact_content' => $compact]);
        }
        return $memory->fresh();
    }

    public function deactivateMemory(AiMemory $memory, User $user): void
    {
        $memory->update(['active' => false]);
    }

    public function ensureScopeSecurity(AiMemory $memory, AgentTask $task): bool
    {
        if ($memory->company_id && $task->company_id && $memory->company_id !== $task->company_id) {
            return false;
        }
        if ($memory->client_id && $task->client_id && $memory->client_id !== $task->client_id) {
            return false;
        }
        return true;
    }

    public function createApprovedPatternFromApproval(
        ?int $companyId,
        ?int $clientId,
        ?int $aiEmployeeId,
        ?int $agentTaskId,
        string $title,
        string $content,
        int $sourceId,
        string $sourceType = 'approval_request'
    ): AiMemory {
        return $this->storeMemory([
            'company_id'      => $companyId,
            'client_id'       => $clientId,
            'ai_employee_id'  => $aiEmployeeId,
            'agent_task_id'   => $agentTaskId,
            'memory_type'     => 'approved_pattern',
            'title'           => $title,
            'content'         => $content,
            'weight'          => 3,
            'relevance_score' => 80,
            'source_type'     => $sourceType,
            'source_id'       => $sourceId,
        ]);
    }

    public function createRejectedPatternFromRejection(
        ?int $companyId,
        ?int $clientId,
        ?int $aiEmployeeId,
        ?int $agentTaskId,
        string $title,
        string $content,
        int $sourceId,
        string $sourceType = 'approval_request'
    ): AiMemory {
        return $this->storeMemory([
            'company_id'      => $companyId,
            'client_id'       => $clientId,
            'ai_employee_id'  => $aiEmployeeId,
            'agent_task_id'   => $agentTaskId,
            'memory_type'     => 'rejected_pattern',
            'title'           => $title,
            'content'         => $content,
            'weight'          => 4,
            'relevance_score' => 90,
            'source_type'     => $sourceType,
            'source_id'       => $sourceId,
        ]);
    }

    public function createFeedbackMemoryFromChangesRequest(
        ?int $companyId,
        ?int $clientId,
        ?int $aiEmployeeId,
        ?int $agentTaskId,
        string $title,
        string $feedback,
        int $sourceId,
        string $sourceType = 'approval_request'
    ): AiMemory {
        return $this->storeFeedbackMemory([
            'company_id'     => $companyId,
            'client_id'      => $clientId,
            'ai_employee_id' => $aiEmployeeId,
            'agent_task_id'  => $agentTaskId,
            'title'          => $title,
            'content'        => $feedback,
            'source_type'    => $sourceType,
            'source_id'      => $sourceId,
        ]);
    }

    public function formatMemoriesForPrompt(Collection $memories): string
    {
        if ($memories->isEmpty()) return '';

        $lines = ["Memórias operacionais relevantes:"];
        foreach ($memories as $mem) {
            $type    = $mem->memory_type_label;
            $content = $mem->getEffectiveContent();
            $lines[] = "- [{$type}] {$content}";
        }
        return implode("\n", $lines);
    }
}
