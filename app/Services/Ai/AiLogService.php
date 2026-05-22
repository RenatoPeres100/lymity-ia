<?php

namespace App\Services\Ai;

use App\Models\AiTask;
use App\Models\AiTaskLog;

class AiLogService
{
    public function log(AiTask $task, string $message, string $level = 'info', array $context = []): AiTaskLog
    {
        return AiTaskLog::create([
            'ai_task_id'      => $task->id,
            'ai_employee_id'  => $task->ai_employee_id,
            'client_id'       => $task->client_id,
            'level'           => $level,
            'message'         => $message,
            'context'         => $context,
        ]);
    }

    public function info(AiTask $task, string $message, array $context = []): AiTaskLog
    {
        return $this->log($task, $message, 'info', $context);
    }

    public function success(AiTask $task, string $message, array $context = []): AiTaskLog
    {
        return $this->log($task, $message, 'success', $context);
    }

    public function warning(AiTask $task, string $message, array $context = []): AiTaskLog
    {
        return $this->log($task, $message, 'warning', $context);
    }

    public function error(AiTask $task, string $message, array $context = []): AiTaskLog
    {
        return $this->log($task, $message, 'error', $context);
    }
}
