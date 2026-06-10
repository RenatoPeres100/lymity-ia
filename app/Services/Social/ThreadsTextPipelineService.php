<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\AgentTask;
use App\Models\AgentTaskRun;
use App\Models\AiEmployee;
use App\Models\ApprovalRequest;
use App\Models\GeneratedContentPackage;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\Threads\ThreadsPublishingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class ThreadsTextPipelineService
{
    public function __construct(
        private ThreadsPublishingService $publisher,
    ) {}

    public function createDraftFromAi(
        array $payload,
        AgentTask $task,
        AiEmployee $employee,
        ?AgentTaskRun $run = null
    ): SocialPost {
        $hashtags = $payload['hashtags'] ?? [];
        if (is_array($hashtags)) {
            $hashtags = implode(' ', array_map(fn($h) => str_starts_with($h, '#') ? $h : '#' . $h, $hashtags));
        }

        $post = SocialPost::create([
            'company_id'        => $task->company_id,
            'client_id'         => $task->client_id,
            'ai_employee_id'    => $employee->id,
            'agent_task_id'     => $task->id,
            'agent_task_run_id' => $run?->id,
            'platform'          => 'threads',
            'content_type'      => 'threads_text',
            'title'             => $payload['title'] ?? $task->title,
            'main_caption'      => $payload['post_text'] ?? $payload['text'] ?? $payload['content'] ?? '',
            'cta'               => $payload['cta'] ?? null,
            'hashtags'          => $hashtags,
            'creative_brief'    => $payload['strategic_angle'] ?? null,
            'status'            => 'pending_approval',
            'requires_approval' => true,
            'metadata'          => [
                'generated_by_task'  => $task->id,
                'ai_employee_id'     => $employee->id,
                'strategic_angle'    => $payload['strategic_angle'] ?? null,
                'approval_notes'     => $payload['approval_notes'] ?? null,
                'content_type'       => 'threads_text',
            ],
        ]);

        $this->log('threads.post.generated_by_ai', null, [
            'post_id'       => $post->id,
            'task_id'       => $task->id,
            'ai_employee'   => $employee->name,
        ]);

        return $post;
    }

    public function createManualDraft(array $data, User $user): SocialPost
    {
        $hashtags = $data['hashtags'] ?? '';
        if (is_array($hashtags)) {
            $hashtags = implode(' ', array_map(fn($h) => str_starts_with($h, '#') ? $h : '#' . $h, $hashtags));
        }

        $post = SocialPost::create([
            'company_id'        => $user->company_id,
            'client_id'         => null,
            'created_by'        => $user->id,
            'platform'          => 'threads',
            'content_type'      => 'threads_text',
            'title'             => $data['title'] ?? 'Post Threads',
            'main_caption'      => $data['main_caption'] ?? $data['post_text'] ?? '',
            'cta'               => $data['cta'] ?? null,
            'hashtags'          => $hashtags,
            'status'            => 'draft',
            'requires_approval' => true,
            'metadata'          => ['created_manually' => true],
        ]);

        $this->log('threads.post.created', $user, ['post_id' => $post->id]);

        return $post;
    }

    public function submitForApproval(SocialPost $post, User $user): ApprovalRequest
    {
        abort_unless($post->platform === 'threads', 422, 'Post não é Threads.');
        abort_unless(in_array($post->status, ['draft', 'rejected']), 422,
            "Post no status '{$post->status}' não pode ser enviado para aprovação.");
        abort_unless(!empty(trim($post->main_caption ?? '')), 422,
            'Texto do post não pode estar vazio.');

        $post->update(['status' => 'pending_approval']);

        $approval = ApprovalRequest::create([
            'company_id'       => $post->company_id,
            'client_id'        => $post->client_id,
            'approvable_type'  => SocialPost::class,
            'approvable_id'    => $post->id,
            'approval_type'    => 'threads_text_post',
            'title'            => 'Aprovação: ' . ($post->title ?? 'Post Threads'),
            'summary'          => mb_substr($post->main_caption ?? '', 0, 200),
            'status'           => 'pending',
            'requested_by'     => $user->id,
            'metadata'         => [
                'platform'     => 'threads',
                'content_type' => 'threads_text',
                'post_id'      => $post->id,
            ],
        ]);

        $this->log('threads.post.submitted_for_approval', $user, [
            'post_id'     => $post->id,
            'approval_id' => $approval->id,
        ]);

        return $approval;
    }

    public function approve(SocialPost $post, User $user): void
    {
        $post->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->log('threads.post.approved', $user, ['post_id' => $post->id]);
    }

    public function reject(SocialPost $post, User $user, ?string $notes): void
    {
        $post->update(['status' => 'rejected']);

        $this->log('threads.post.rejected', $user, [
            'post_id' => $post->id,
            'notes'   => $notes,
        ]);
    }

    public function schedule(SocialPost $post, Carbon $scheduledAt, User $user): void
    {
        abort_unless(in_array($post->status, ['approved', 'draft', 'pending_approval']), 422,
            "Post no status '{$post->status}' não pode ser agendado diretamente.");
        abort_unless($post->platform === 'threads', 422, 'Post não é Threads.');

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);

        $this->log('threads.post.scheduled', $user, [
            'post_id'      => $post->id,
            'scheduled_at' => $scheduledAt->toISOString(),
        ]);
    }

    public function publishDue(): void
    {
        $channel = SocialChannel::where('platform', 'threads')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->where('status', 'connected')
            ->first();

        if (!$channel) {
            Log::info('[ThreadsPipeline] Canal Threads não conectado. Nenhum post publicado.');
            return;
        }

        if (!config('threads.publishing_enabled', false)) {
            Log::info('[ThreadsPipeline] THREADS_PUBLISHING_ENABLED=false — publicação bloqueada.');
            return;
        }

        $due = SocialPost::where('platform', 'threads')
            ->where('content_type', 'threads_text')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->whereNotNull('approved_by')
            ->get();

        foreach ($due as $post) {
            try {
                $this->publisher->publishTextPost($post);
            } catch (Throwable $e) {
                $this->publisher->handleError($e, $post);
                Log::error("[ThreadsPipeline] Post #{$post->id} failed: " . $e->getMessage());
            }
        }
    }

    public function markAsPublished(SocialPost $post, array $response): void
    {
        $post->update([
            'status'            => 'published',
            'published_at'      => now(),
            'external_post_id'  => $response['id'] ?? null,
            'publication_error' => null,
        ]);
    }

    public function markAsFailed(SocialPost $post, string $error): void
    {
        $safe = preg_replace('/THQA[A-Za-z0-9_\-]+|EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $error);
        $post->update([
            'status'            => 'failed',
            'publication_error' => $safe,
        ]);
    }

    private function log(string $action, ?User $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'threads',
                'level'       => str_contains($action, 'fail') || str_contains($action, 'reject') ? 'warning' : 'info',
                'description' => "Threads: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (Throwable) {}
    }
}
