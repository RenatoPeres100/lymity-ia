<?php

namespace App\Services\AI\Context;

use App\Models\AgentTask;

class AgentTaskContextService
{
    public function generateTaskHash(AgentTask $task): string
    {
        $data = implode('|', array_filter([
            $task->title,
            $task->description,
            $task->operational_instructions,
            $task->task_type,
            $task->content_channel,
            $task->content_format,
            $task->frequency,
            implode(',', $task->days_of_week ?? []),
            $task->time_of_day,
            $task->requires_image ? '1' : '0',
            $task->image_type,
            (string) $task->carousel_slides_count,
            $task->image_instructions,
            json_encode($task->expected_output),
        ]));
        return hash('sha256', $data);
    }

    public function needsCompactRefresh(AgentTask $task): bool
    {
        if (!$task->compact_task_context) return true;
        if (!$task->task_context_hash) return true;
        return $task->task_context_hash !== $this->generateTaskHash($task);
    }

    public function buildCompactTaskContext(AgentTask $task): string
    {
        $parts = [];

        $parts[] = "Tarefa: {$task->title}";
        $parts[] = "Tipo: {$task->task_type_label}";

        if ($task->description) {
            $parts[] = "Descrição: " . mb_substr($task->description, 0, 200);
        }

        if ($task->operational_instructions) {
            $instructions = mb_substr($task->operational_instructions, 0, 400);
            $parts[] = "Instruções: {$instructions}";
        }

        if ($task->content_channel) {
            $parts[] = "Canal: {$task->content_channel}";
        }

        if ($task->content_format) {
            $parts[] = "Formato: {$task->content_format}";
        }

        // Recurrence
        if ($task->frequency !== 'manual') {
            $recurrence = "Recorrência: {$task->frequency_label}";
            if ($task->days_of_week) {
                $recurrence .= " ({$task->days_label})";
            }
            if ($task->time_of_day) {
                $recurrence .= " às {$task->time_of_day}";
            }
            $parts[] = $recurrence;
        }

        // Research
        if ($task->requires_external_research) {
            $topics = $task->external_research_topics ?? 'tópicos relevantes';
            $days   = $task->external_research_recency_days ?? 7;
            $parts[] = "Pesquisa externa: últimos {$days} dias sobre {$topics}";
        }

        // Image
        if ($task->requires_image) {
            $imgType = $task->image_type ?? 'imagem';
            $imgPart = "Requer imagem: {$imgType}";
            if ($task->carousel_slides_count) {
                $imgPart .= " ({$task->carousel_slides_count} slides)";
            }
            if ($task->image_instructions) {
                $imgPart .= '. ' . mb_substr($task->image_instructions, 0, 150);
            }
            $parts[] = $imgPart;
        }

        // Approval
        if ($task->requires_approval) {
            $parts[] = "Requer aprovação antes de publicar";
        }

        return implode('. ', array_map(fn($p) => rtrim($p, '.'), $parts)) . '.';
    }

    public function refreshCompactTaskContext(AgentTask $task): AgentTask
    {
        $hash    = $this->generateTaskHash($task);
        $compact = $this->buildCompactTaskContext($task);

        $task->update([
            'task_context_hash'                  => $hash,
            'compact_task_context'               => $compact,
            'compact_task_context_generated_at'  => now(),
        ]);

        return $task->fresh();
    }

    public function getCompactTaskContext(AgentTask $task): string
    {
        if ($this->needsCompactRefresh($task)) {
            $task = $this->refreshCompactTaskContext($task);
        }
        return $task->compact_task_context ?: $this->buildCompactTaskContext($task);
    }
}
