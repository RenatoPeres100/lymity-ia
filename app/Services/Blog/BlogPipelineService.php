<?php

namespace App\Services\Blog;

use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

class BlogPipelineService
{
    // ── Draft creation ─────────────────────────────────────────────────────────

    public function createDraftFromAi(array $data, User $user): BlogPost|array
    {
        $provider     = config('ai.provider', 'mock');
        $realEnabled  = config('ai.real_enabled', false);

        if ($provider === 'mock' || !$realEnabled) {
            return ['error' => 'Provider de IA não configurado. Configure um provedor real (AI_PROVIDER e AI_REAL_ENABLED=true) para gerar conteúdo.'];
        }

        $blogWriter = AiEmployee::where('slug', 'blog-writer-ia')->where('status', 'active')->first();
        if (!$blogWriter) {
            return ['error' => 'Funcionário Blog Writer IA não encontrado ou inativo.'];
        }

        // Build the AI prompt
        $promptParts = [];
        if (!empty($data['tema']))          $promptParts[] = "Tema: {$data['tema']}";
        if (!empty($data['keyword']))       $promptParts[] = "Palavra-chave principal: {$data['keyword']}";
        if (!empty($data['keywords_sec'])) $promptParts[] = "Palavras-chave secundárias: {$data['keywords_sec']}";
        if (!empty($data['objetivo']))     $promptParts[] = "Objetivo: {$data['objetivo']}";
        if (!empty($data['tom']))          $promptParts[] = "Tom: {$data['tom']}";
        if (!empty($data['publico']))      $promptParts[] = "Público-alvo: {$data['publico']}";
        if (!empty($data['cta']))          $promptParts[] = "CTA desejado: {$data['cta']}";

        $promptParts[] = "\nRetorne um JSON com os campos: title, subtitle, slug, excerpt, seo_title, seo_description, focus_keyword, secondary_keywords, content (HTML com H2/H3 e CTA final).";

        $task = AiTask::create([
            'ai_employee_id' => $blogWriter->id,
            'task_type'      => 'generate_blog_post',
            'title'          => 'Gerar artigo: ' . ($data['tema'] ?? 'sem tema'),
            'input_data'     => implode("\n", $promptParts),
            'status'         => 'pending',
            'requires_approval' => true,
            'client_id'      => null,
        ]);

        // Dispatch to queue for real generation
        \App\Jobs\RunAiTaskJob::dispatch($task->id);

        // Create draft post linked to the task — content will be filled by job
        $post = BlogPost::create([
            'title'           => $data['tema'] ?? 'Artigo gerado por IA',
            'slug'            => Str::slug($data['tema'] ?? 'artigo-ia-' . now()->format('YmdHis')),
            'type'            => 'agency',
            'status'          => 'draft',
            'author_id'       => $user->id,
            'ai_employee_id'  => $blogWriter->id,
            'focus_keyword'      => $data['keyword'] ?? null,
            'secondary_keywords' => $this->parseStringToArray($data['keywords_sec'] ?? null),
            'content'         => '',
            'excerpt'         => '',
        ]);

        // Link task to post via metadata
        $task->update(['metadata' => array_merge($task->metadata ?? [], ['blog_post_id' => $post->id])]);

        $this->registerLog($post, 'ai_generation_requested', $user, ['ai_task_id' => $task->id]);

        return $post;
    }

    public function createManualDraft(array $data, User $user): BlogPost
    {
        $slug = $data['slug'] ?? Str::slug($data['title']);

        // Ensure unique slug
        $base = $slug;
        $n    = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        $post = BlogPost::create([
            'title'              => $data['title'],
            'slug'               => $slug,
            'subtitle'           => $data['subtitle'] ?? null,
            'excerpt'            => $data['excerpt'] ?? null,
            'content'            => $data['content'] ?? '',
            'featured_image'     => $data['featured_image'] ?? null,
            'category_id'        => $data['category_id'] ?? null,
            'author_id'          => $user->id,
            'type'               => 'agency',
            'status'             => 'draft',
            'seo_title'          => $data['seo_title'] ?? null,
            'seo_description'    => $data['seo_description'] ?? null,
            'focus_keyword'      => $data['focus_keyword'] ?? null,
            'secondary_keywords' => $this->parseStringToArray($data['secondary_keywords'] ?? null),
            'tags'               => $this->parseStringToArray($data['tags'] ?? null),
        ]);

        $this->registerLog($post, 'draft_created', $user);

        return $post;
    }

    // ── Approval flow ──────────────────────────────────────────────────────────

    public function submitForApproval(BlogPost $post, User $user): BlogPost
    {
        abort_unless($post->canSubmitForApproval(), 422, "Post com status '{$post->status}' não pode ser enviado para aprovação.");

        $post->update(['status' => 'pending_approval']);

        // Create ApprovalRequest if one doesn't already exist pending
        $existing = ApprovalRequest::where('approvable_type', BlogPost::class)
            ->where('approvable_id', $post->id)
            ->where('status', 'pending')
            ->first();

        if (!$existing) {
            ApprovalRequest::create([
                'requested_by'   => $user->id,
                'approvable_type'=> BlogPost::class,
                'approvable_id'  => $post->id,
                'title'          => 'Aprovação: ' . $post->title,
                'description'    => "Post de blog aguardando aprovação para publicação.",
                'approval_type'  => 'blog',
                'status'         => 'pending',
                'sensitive_level'=> 'low',
            ]);
        }

        $this->registerLog($post, 'submitted_for_approval', $user);

        return $post;
    }

    public function approve(BlogPost $post, User $user): BlogPost
    {
        abort_unless($post->canBeApproved(), 422, "Post com status '{$post->status}' não pode ser aprovado.");

        $futureScheduled = $post->scheduled_at && $post->scheduled_at->isFuture();
        $targetStatus    = $futureScheduled ? 'scheduled' : 'approved';

        $post->update([
            'status'      => $targetStatus,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Also approve pending ApprovalRequest
        ApprovalRequest::where('approvable_type', BlogPost::class)
            ->where('approvable_id', $post->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

        $this->registerLog($post, 'approved', $user);

        if ($futureScheduled) {
            $this->registerLog($post, 'blog_post_scheduled_after_approval', $user, [
                'scheduled_at' => $post->scheduled_at->toIso8601String(),
            ]);
        }

        return $post;
    }

    public function reject(BlogPost $post, User $user, ?string $reason = null): BlogPost
    {
        $post->update(['status' => 'rejected']);

        ApprovalRequest::where('approvable_type', BlogPost::class)
            ->where('approvable_id', $post->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
            ]);

        $this->registerLog($post, 'rejected', $user, ['reason' => $reason]);

        return $post;
    }

    // ── Scheduling ─────────────────────────────────────────────────────────────

    public function schedule(BlogPost $post, Carbon|string $scheduledAt, User $user): BlogPost
    {
        abort_unless($post->canBeScheduled(), 422, "Apenas posts aprovados podem ser agendados.");

        $at = $scheduledAt instanceof Carbon ? $scheduledAt : Carbon::parse($scheduledAt);

        abort_if($at->isPast(), 422, 'A data de publicação deve estar no futuro.');

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $at,
        ]);

        $this->registerLog($post, 'scheduled', $user, ['scheduled_at' => $at->toIso8601String()]);

        return $post;
    }

    // ── Publication ────────────────────────────────────────────────────────────

    public function publish(BlogPost $post, ?User $user = null): BlogPost
    {
        abort_unless($post->canBePublished(), 422, "Post com status '{$post->status}' não pode ser publicado.");

        // Validate required fields
        if (empty(trim($post->title ?? ''))) {
            return $this->failPublication($post, 'Título obrigatório para publicação.');
        }
        if (empty(trim($post->slug ?? ''))) {
            return $this->failPublication($post, 'Slug obrigatório para publicação.');
        }
        if (empty(trim(strip_tags($post->content ?? '')))) {
            return $this->failPublication($post, 'Conteúdo obrigatório para publicação.');
        }

        try {
            $post->update([
                'status'             => 'publishing',
                'publication_error'  => null,
            ]);

            $post->update([
                'status'       => 'published',
                'published_at' => $post->published_at ?? now(),
            ]);

            $this->registerLog($post, 'published', $user);
        } catch (Throwable $e) {
            $this->failPublication($post, $e->getMessage());
        }

        return $post->fresh();
    }

    public function publishNow(BlogPost $post, User $user): BlogPost
    {
        abort_unless($post->isApproved(), 422, "Apenas posts aprovados podem ser publicados imediatamente.");

        return $this->publish($post, $user);
    }

    public function failPublication(BlogPost $post, Throwable|string $error): BlogPost
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;

        $post->update([
            'status'            => 'failed',
            'publication_error' => $message,
        ]);

        $this->registerLog($post, 'publication_failed', null, ['error' => $message]);

        return $post->fresh();
    }

    // ── Phase aliases (approvePost / rejectPost / schedulePost / publishPost) ──

    public function approvePost(BlogPost $post, User $user): BlogPost
    {
        return $this->approve($post, $user);
    }

    public function rejectPost(BlogPost $post, User $user, ?string $reason = null): BlogPost
    {
        $post->update([
            'status'         => 'rejected',
            'revision_notes' => $reason,
        ]);

        ApprovalRequest::where('approvable_type', BlogPost::class)
            ->where('approvable_id', $post->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
            ]);

        $this->registerLog($post, 'rejected', $user, ['reason' => $reason]);
        return $post->fresh();
    }

    public function schedulePost(BlogPost $post, Carbon|string $scheduledAt, User $user): BlogPost
    {
        $at = $scheduledAt instanceof Carbon ? $scheduledAt : Carbon::parse($scheduledAt);
        abort_unless($post->canBeScheduled(), 422, "Apenas posts aprovados podem ser agendados.");

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $at,
        ]);

        $this->registerLog($post, 'scheduled', $user, ['scheduled_at' => $at->toIso8601String()]);
        return $post->fresh();
    }

    public function publishPost(BlogPost $post, ?User $user = null): BlogPost
    {
        return $this->publish($post, $user);
    }

    public function publishDuePosts(): array
    {
        $due = BlogPost::agency()->dueForPublishing()->get();
        $published = 0;
        $failed = 0;

        foreach ($due as $post) {
            try {
                $this->publish($post);
                $post->refresh();
                $post->isPublished() ? $published++ : $failed++;
            } catch (\Throwable $e) {
                $this->failPublication($post, $e->getMessage());
                $failed++;
            }
        }

        return ['published' => $published, 'failed' => $failed];
    }

    private function parseStringToArray(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $items = array_values(array_filter(array_map('trim', explode(',', $value))));
        return empty($items) ? null : $items;
    }

    // ── Logging ────────────────────────────────────────────────────────────────

    public function registerLog(BlogPost $post, string $action, ?User $user = null, array $metadata = []): void
    {
        ActivityLog::create([
            'user_id'      => $user?->id,
            'action'       => $action,
            'module'       => 'blog',
            'level'        => str_contains($action, 'fail') ? 'error' : 'info',
            'description'  => "Blog post #{$post->id} — {$post->title} — {$action}",
            'subject_type' => BlogPost::class,
            'subject_id'   => $post->id,
            'metadata'     => array_merge(['blog_post_id' => $post->id, 'status' => $post->status], $metadata),
        ]);
    }
}
