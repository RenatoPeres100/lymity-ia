<?php

namespace App\Services\Social;

use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\SocialPost;
use App\Models\SocialPostVariant;
use App\Models\User;
use App\Services\Ai\AiTaskService;
use Illuminate\Support\Facades\Auth;

class SocialAiService
{
    public function __construct(
        private AiTaskService  $taskService,
        private SocialPostService $postService,
    ) {}

    public function requestPostGeneration(array $data, User $user): AiTask
    {
        $employee = AiEmployee::where('slug', 'social-media-ia')
            ->where('status', 'active')
            ->first();

        if (!$employee) {
            $employee = AiEmployee::where('status', 'active')->first();
        }

        if (!$employee) {
            throw new \RuntimeException('Nenhum funcionário IA ativo disponível para geração de post social.');
        }

        $task = AiTask::create([
            'ai_employee_id'   => $employee->id,
            'client_id'        => $data['client_id'] ?? null,
            'created_by'       => $user->id,
            'title'            => $data['title'],
            'description'      => $data['prompt'] ?? "Gerar post social: objetivo {$data['objective']}, formato {$data['content_type']}.",
            'task_type'        => 'generate_social_post',
            'priority'         => 'medium',
            'requires_approval'=> $data['requires_approval'] ?? true,
            'sensitive_action' => false,
            'status'           => 'queued',
            'metadata'         => [
                'objective'          => $data['objective'] ?? 'authority',
                'content_type'       => $data['content_type'] ?? 'feed',
                'client_id'          => $data['client_id'] ?? null,
                'company_id'         => $data['company_id'] ?? null,
                'auto_create_post'   => true,
            ],
        ]);

        $task = $this->taskService->runTask($task);

        if ($task->status === 'completed' || $task->status === 'waiting_approval') {
            $this->createPostFromAiTask($task, $user, $data);
        }

        return $task->fresh();
    }

    public function createPostFromAiTask(AiTask $task, ?User $user = null, array $extra = []): ?SocialPost
    {
        if (!$task->output) {
            return null;
        }

        $metadata = $task->metadata ?? [];
        $requiresApproval = $task->requires_approval;

        $post = $this->postService->create([
            'client_id'        => $task->client_id ?? $metadata['client_id'] ?? null,
            'company_id'       => $metadata['company_id'] ?? null,
            'ai_employee_id'   => $task->ai_employee_id,
            'title'            => $task->title,
            'objective'        => $metadata['objective'] ?? $extra['objective'] ?? 'authority',
            'content_type'     => $metadata['content_type'] ?? $extra['content_type'] ?? 'feed',
            'main_caption'     => $task->output,
            'creative_brief'   => $task->description,
            'status'           => $requiresApproval ? 'pending_approval' : 'draft',
            'requires_approval'=> $requiresApproval,
            'metadata'         => ['ai_task_id' => $task->id],
        ], $user);

        if ($requiresApproval && $user) {
            try {
                app(\App\Services\Approval\ApprovalService::class)->createApproval([
                    'client_id'       => $post->client_id,
                    'requested_by'    => $user->id,
                    'ai_employee_id'  => $post->ai_employee_id,
                    'approvable_type' => SocialPost::class,
                    'approvable_id'   => $post->id,
                    'title'           => "Aprovar post IA: {$post->title}",
                    'description'     => substr($task->output, 0, 500),
                    'approval_type'   => 'post',
                    'sensitive_level' => 'low',
                    'payload'         => [
                        'ai_task_id'   => $task->id,
                        'objective'    => $post->objective,
                        'content_type' => $post->content_type,
                    ],
                ]);
            } catch (\Throwable $e) {
                // Log mas não bloqueia criação do post
                \Illuminate\Support\Facades\Log::warning("ApprovalRequest não criada para SocialPost #{$post->id}: " . $e->getMessage());
            }
        }

        return $post;
    }

    public function generateVariants(SocialPost $post, array $platforms, User $user): array
    {
        $employee = $post->aiEmployee ?? AiEmployee::where('slug', 'social-media-ia')->first();

        if ($employee) {
            $task = AiTask::create([
                'ai_employee_id'   => $employee->id,
                'client_id'        => $post->client_id,
                'created_by'       => $user->id,
                'title'            => "Variações: {$post->title}",
                'description'      => $post->main_caption ?? $post->creative_brief,
                'task_type'        => 'generate_social_variants',
                'priority'         => 'low',
                'requires_approval'=> false,
                'sensitive_action' => false,
                'status'           => 'queued',
            ]);

            try {
                $this->taskService->runTask($task);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Variant task failed: " . $e->getMessage());
            }
        }

        return app(SocialPostService::class)->generateVariants($post, $platforms);
    }

    public function improvePost(SocialPost $post, string $instruction, User $user): AiTask
    {
        $employee = $post->aiEmployee ?? AiEmployee::where('slug', 'social-media-ia')->first();

        if (!$employee) {
            throw new \RuntimeException('Nenhum funcionário IA disponível.');
        }

        $task = AiTask::create([
            'ai_employee_id'   => $employee->id,
            'client_id'        => $post->client_id,
            'created_by'       => $user->id,
            'title'            => "Melhorar: {$post->title}",
            'description'      => "Instrução: {$instruction}\n\nConteúdo atual:\n{$post->main_caption}",
            'task_type'        => 'improve_social_post',
            'priority'         => 'medium',
            'requires_approval'=> true,
            'sensitive_action' => false,
            'status'           => 'queued',
        ]);

        return $this->taskService->runTask($task)->fresh();
    }
}
