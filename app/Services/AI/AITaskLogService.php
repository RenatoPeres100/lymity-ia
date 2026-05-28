<?php

namespace App\Services\AI;

use App\Models\AiEmployee;
use App\Models\AiTaskLog;
use App\Services\AI\DTO\AITextResponse;
use App\Services\AI\DTO\TokenEstimate;
use Throwable;

class AITaskLogService
{
    public function start(array $data): AiTaskLog
    {
        return AiTaskLog::create(array_merge([
            'task_type'  => $data['task_type'] ?? null,
            'status'     => 'running',
            'provider'   => $data['provider'] ?? config('ai.provider'),
            'model'      => $data['model'] ?? config('ai.gemini_text_model'),
            'started_at' => now(),
            'message'    => $data['message'] ?? 'Iniciando geração...',
            'ai_employee_id' => $data['agent_id'] ?? null,
            'related_type'   => $data['related_type'] ?? null,
            'related_id'     => $data['related_id'] ?? null,
            'payload_snapshot' => $this->safeSnapshot($data['payload'] ?? []),
        ], $data));
    }

    public function success(AiTaskLog $log, array $data = []): AiTaskLog
    {
        $log->update(array_merge([
            'status'             => 'success',
            'finished_at'        => now(),
            'input_tokens'       => $data['input_tokens'] ?? null,
            'output_tokens'      => $data['output_tokens'] ?? null,
            'estimated_cost_usd' => $data['estimated_cost_usd'] ?? null,
            'model'              => $data['model'] ?? $log->model,
        ], $data));

        return $log->fresh();
    }

    public function fail(AiTaskLog $log, Throwable|string $error, array $data = []): AiTaskLog
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;

        $log->update(array_merge([
            'status'        => 'error',
            'finished_at'   => now(),
            'error_message' => $message,
            'level'         => 'error',
        ], $data));

        return $log->fresh();
    }

    public function logGeneration(array $data): AiTaskLog
    {
        return AiTaskLog::create([
            'task_type'          => $data['task_type'] ?? null,
            'status'             => $data['status'] ?? 'success',
            'provider'           => $data['provider'] ?? 'google',
            'model'              => $data['model'] ?? null,
            'input_tokens'       => $data['input_tokens'] ?? null,
            'output_tokens'      => $data['output_tokens'] ?? null,
            'estimated_cost_usd' => $data['estimated_cost_usd'] ?? null,
            'started_at'         => $data['started_at'] ?? now(),
            'finished_at'        => now(),
            'message'            => $data['message'] ?? null,
            'ai_employee_id'     => $data['agent_id'] ?? null,
            'related_type'       => $data['related_type'] ?? null,
            'related_id'         => $data['related_id'] ?? null,
            'payload_snapshot'   => $this->safeSnapshot($data['payload'] ?? []),
            'error_message'      => $data['error_message'] ?? null,
            'level'              => $data['level'] ?? 'info',
        ]);
    }

    public function fromResponse(AITextResponse $response, array $context = []): AiTaskLog
    {
        return $this->logGeneration([
            'task_type'          => $context['task_type'] ?? null,
            'status'             => $response->success ? 'success' : 'error',
            'provider'           => $response->provider,
            'model'              => $response->model,
            'input_tokens'       => $response->input_tokens,
            'output_tokens'      => $response->output_tokens,
            'estimated_cost_usd' => $response->estimated_cost_usd,
            'message'            => $response->success ? 'Geração concluída' : $response->error_message,
            'error_message'      => $response->success ? null : $response->error_message,
            'agent_id'           => $context['agent_id'] ?? null,
            'related_type'       => $context['related_type'] ?? null,
            'related_id'         => $context['related_id'] ?? null,
            'payload'            => $context['payload'] ?? [],
            'level'              => $response->success ? 'info' : 'error',
            'started_at'         => $context['started_at'] ?? now(),
        ]);
    }

    private function safeSnapshot(array $data): array
    {
        // Never store API keys or secrets
        $forbidden = ['key', 'token', 'secret', 'password', 'api_key', 'gemini_api_key'];
        return array_filter($data, fn($k) => !in_array(strtolower($k), $forbidden), ARRAY_FILTER_USE_KEY);
    }
}
