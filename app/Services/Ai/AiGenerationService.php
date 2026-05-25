<?php

namespace App\Services\Ai;

use App\Models\ActivityLog;
use App\Models\AiMemory;
use App\Models\AiProviderCall;
use App\Models\AiTask;
use App\Models\ClientBrandProfile;
use App\Models\ClientKnowledgeBase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiGenerationService
{
    public function __construct(
        private AiProviderManager $manager,
        private AiCostService     $costService,
        private AiLogService      $logger,
    ) {}

    /**
     * Build context payload from an AiTask and call the resolved provider.
     * Records AiProviderCall + AiTaskLog.
     * Updates task output/status/error.
     */
    public function generateForTask(AiTask $task): array
    {
        $provider = config('ai.provider', 'mock');

        // Enforce limits for real providers
        if ($provider !== 'mock') {
            if (!$this->costService->canRunTask($task->client)) {
                $msg = 'Limite de uso de IA atingido. Verifique AI_DAILY_TASK_LIMIT e AI_MONTHLY_BUDGET_LIMIT.';
                $this->costService->logLimitWarning($provider);
                return $this->failResult($msg);
            }
        }

        $payload = $this->buildPayload($task);
        $result  = $this->manager->generateFromPayload($payload);

        $this->recordCall($task, $result);

        if ($result['success']) {
            $task->update(['output' => $result['response']]);
            $this->logger->success($task, "Geração concluída via {$result['provider']}/{$result['model']}.", [
                'tokens' => $result['total_tokens'],
                'cost'   => $result['estimated_cost'],
            ]);

            $this->logActivity($task, $result);
        } else {
            $task->update(['error_message' => $result['error_message']]);
            $this->logger->error($task, "Erro na geração: " . $result['error_message']);
            Log::warning('[AiGenerationService] generateForTask failed', [
                'task_id'  => $task->id,
                'provider' => $result['provider'],
                'error'    => $result['error_message'],
            ]);
        }

        return $result;
    }

    /**
     * Direct generation without an AiTask — used for connection test and ad-hoc calls.
     */
    public function generateDirect(array $payload): array
    {
        $result = $this->manager->generateFromPayload($payload);

        AiProviderCall::create([
            'user_id'          => Auth::id(),
            'provider'         => $result['provider'],
            'model'            => $result['model'],
            'prompt_preview'   => mb_substr($result['prompt_preview'] ?? '', 0, 500),
            'response_summary' => mb_substr($result['response_summary'] ?? '', 0, 500),
            'status'           => $result['success'] ? 'success' : 'failed',
            'input_tokens'     => $result['input_tokens'],
            'output_tokens'    => $result['output_tokens'],
            'total_tokens'     => $result['total_tokens'],
            'estimated_cost'   => $result['estimated_cost'],
            'error_message'    => $result['error_message'],
            'metadata'         => ['source' => 'direct'],
        ]);

        return $result;
    }

    private function buildPayload(AiTask $task): array
    {
        $employee = $task->employee;
        $client   = $task->client;

        // Client context
        $clientContext = [];
        if ($client) {
            $clientContext = [
                'name'            => $client->name,
                'segment'         => $client->segment ?? '',
                'brand_voice'     => $client->brand_voice ?? '',
                'target_audience' => $client->target_audience ?? '',
            ];

            // Brand profile
            $profile = ClientBrandProfile::where('client_id', $client->id)->first();
            if ($profile) {
                $clientContext['tone_of_voice']   = $profile->tone_of_voice ?? '';
                $clientContext['target_audience'] = $profile->target_audience ?? $clientContext['target_audience'];
                $clientContext['visual_style']    = $profile->visual_style ?? '';
                $clientContext['main_offer']      = $profile->main_offer ?? '';
            }
        }

        // Memories (last 5 active)
        $memories = [];
        if ($employee) {
            $memories = AiMemory::where('ai_employee_id', $employee->id)
                ->latest()
                ->take(5)
                ->pluck('content')
                ->toArray();
        }

        // Knowledge base snippets (first 2)
        $kbSnippets = [];
        if ($client) {
            $kbSnippets = ClientKnowledgeBase::where('client_id', $client->id)
                ->where('status', 'active')
                ->take(2)
                ->pluck('content')
                ->map(fn ($c) => mb_substr($c, 0, 400))
                ->toArray();
        }

        return [
            'task_type'      => $task->getEffectiveTaskType(),
            'title'          => $task->title,
            'description'    => $task->description,
            'client_name'    => $client?->name ?? 'Lymity IA',
            'client_context' => $clientContext,
            'employee'       => $employee,
            'system_prompt'  => $employee?->system_prompt ?? '',
            'memories'       => $memories,
            'knowledge_base' => $kbSnippets,
            'user_message'   => $task->description,
        ];
    }

    private function recordCall(AiTask $task, array $result): void
    {
        $promptPreview = $result['prompt_preview'] ?? '';

        AiProviderCall::create([
            'ai_employee_id'   => $task->ai_employee_id,
            'ai_task_id'       => $task->id,
            'client_id'        => $task->client_id,
            'user_id'          => $task->created_by ?? Auth::id(),
            'provider'         => $result['provider'],
            'model'            => $result['model'],
            'prompt_hash'      => $promptPreview ? hash('sha256', $promptPreview) : null,
            'prompt_preview'   => mb_substr($promptPreview, 0, 500),
            'response_summary' => mb_substr($result['response_summary'] ?? '', 0, 500),
            'status'           => $result['success'] ? 'success' : 'failed',
            'input_tokens'     => $result['input_tokens'],
            'output_tokens'    => $result['output_tokens'],
            'total_tokens'     => $result['total_tokens'],
            'estimated_cost'   => $result['estimated_cost'],
            'error_message'    => $result['error_message'],
            'metadata'         => ['task_type' => $task->getEffectiveTaskType()],
        ]);
    }

    private function logActivity(AiTask $task, array $result): void
    {
        ActivityLog::create([
            'company_id'   => $task->client?->company_id,
            'user_id'      => $task->created_by ?? Auth::id(),
            'action'       => 'ai_generation_completed',
            'subject_type' => AiTask::class,
            'subject_id'   => $task->id,
            'description'  => "Geração IA concluída: {$task->title} via {$result['provider']}",
            'metadata'     => [
                'provider' => $result['provider'],
                'model'    => $result['model'],
                'tokens'   => $result['total_tokens'],
                'cost'     => $result['estimated_cost'],
            ],
            'level' => 'info',
        ]);
    }

    private function failResult(string $message): array
    {
        return [
            'success'          => false,
            'provider'         => config('ai.provider', 'mock'),
            'model'            => config('ai.model', ''),
            'prompt_preview'   => '',
            'response'         => '',
            'response_summary' => '',
            'input_tokens'     => null,
            'output_tokens'    => null,
            'total_tokens'     => null,
            'estimated_cost'   => null,
            'error_message'    => $message,
            'raw_response'     => null,
        ];
    }
}
