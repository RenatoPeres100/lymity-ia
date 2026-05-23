<?php

namespace App\Services\Seo;

use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\SeoCluster;
use App\Models\SeoContentPlan;
use App\Models\SeoKeyword;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Support\Str;

class SeoContentService
{
    public function __construct(
        private SeoAiService    $aiService,
        private ApprovalService $approvalService
    ) {}

    public function createKeyword(array $data): SeoKeyword
    {
        return SeoKeyword::create([
            'client_id'     => $data['client_id'] ?? null,
            'company_id'    => $data['company_id'] ?? null,
            'keyword'       => $data['keyword'],
            'search_intent' => $data['search_intent'] ?? 'informational',
            'priority'      => $data['priority'] ?? 'medium',
            'difficulty'    => $data['difficulty'] ?? null,
            'volume'        => $data['volume'] ?? null,
            'status'        => $data['status'] ?? 'planned',
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    public function createCluster(array $data): SeoCluster
    {
        return SeoCluster::create([
            'client_id'    => $data['client_id'] ?? null,
            'company_id'   => $data['company_id'] ?? null,
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'main_keyword' => $data['main_keyword'],
            'status'       => $data['status'] ?? 'active',
        ]);
    }

    public function createContentPlan(array $data): SeoContentPlan
    {
        return SeoContentPlan::create([
            'client_id'   => $data['client_id'] ?? null,
            'company_id'  => $data['company_id'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'month'       => $data['month'],
            'year'        => $data['year'],
            'status'      => $data['status'] ?? 'draft',
        ]);
    }

    public function generateBlogPost(array $data, User $user): BlogPost
    {
        $keyword   = $data['keyword'];
        $title     = $data['title'] ?? "Guia Completo: {$keyword}";
        $clientId  = $data['client_id'] ?? null;
        $type      = $data['type'] ?? 'agency';

        $task = $this->aiService->createAndRunTask(
            'generate_blog_post',
            $title,
            $keyword,
            $clientId,
            $user,
            ['type' => $type, 'keyword' => $keyword]
        );

        $parsed = $this->parseBlogPostOutput($task->output);

        $requiresApproval = $data['requires_approval'] ?? true;
        $status = $requiresApproval ? 'pending_approval' : 'draft';

        $slug = $this->uniqueSlug($parsed['slug'] ?? Str::slug($title));

        $secondaryKeywords = $parsed['secondary_keywords'] ?? null;
        if (is_array($secondaryKeywords)) {
            $secondaryKeywords = implode(', ', $secondaryKeywords);
        }

        $post = BlogPost::create([
            'title'               => $parsed['title'] ?? $title,
            'slug'                => $slug,
            'subtitle'            => $parsed['subtitle'] ?? null,
            'excerpt'             => $parsed['excerpt'] ?? null,
            'content'             => $parsed['content'] ?? $task->output,
            'author_id'           => $user->id,
            'client_id'           => $clientId,
            'type'                => $type,
            'status'              => $status,
            'seo_title'           => $parsed['seo_title'] ?? null,
            'seo_description'     => $parsed['seo_description'] ?? null,
            'focus_keyword'       => $parsed['focus_keyword'] ?? $keyword,
            'secondary_keywords'  => $secondaryKeywords,
        ]);

        if ($requiresApproval) {
            $this->approvalService->createApproval([
                'client_id'       => $clientId,
                'requested_by'    => $user->id,
                'ai_employee_id'  => $task->ai_employee_id,
                'approvable_type' => BlogPost::class,
                'approvable_id'   => $post->id,
                'title'           => "Aprovar post IA: {$post->title}",
                'description'     => "Post gerado por IA com foco na palavra-chave: {$keyword}.",
                'approval_type'   => 'blog',
                'sensitive_level' => 'low',
            ]);
        }

        $this->logActivity($user, $clientId, 'blog_post_ai_generated', "Post IA criado: {$post->title}");

        return $post->fresh();
    }

    public function sendBlogPostToApproval(BlogPost $post, User $user): BlogPost
    {
        if (!in_array($post->status, ['draft', 'rejected'])) {
            throw new \RuntimeException('O post deve estar como rascunho ou rejeitado para enviar para aprovação.');
        }

        $post->update(['status' => 'pending_approval']);

        $this->approvalService->createApproval([
            'client_id'       => $post->client_id,
            'requested_by'    => $user->id,
            'approvable_type' => BlogPost::class,
            'approvable_id'   => $post->id,
            'title'           => "Aprovar post: {$post->title}",
            'description'     => $post->excerpt ?? substr(strip_tags($post->content), 0, 300),
            'approval_type'   => 'blog',
            'sensitive_level' => 'low',
        ]);

        $this->logActivity($user, $post->client_id, 'blog_post_sent_approval', "Post enviado para aprovação: {$post->title}");

        return $post->fresh();
    }

    public function publishBlogPost(BlogPost $post, User $user): BlogPost
    {
        if ($post->status !== 'approved' && !($user->role === 'admin_geral' && $post->type === 'agency')) {
            throw new \RuntimeException('O post deve estar aprovado para ser publicado.');
        }

        $post->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->logActivity($user, $post->client_id, 'blog_post_published', "Post publicado: {$post->title}");

        return $post->fresh();
    }

    private function parseBlogPostOutput(?string $output): array
    {
        if (!$output) {
            return [];
        }

        $decoded = json_decode($output, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['content' => $output];
    }

    private function uniqueSlug(string $slug): string
    {
        $original = $slug;
        $counter  = 1;

        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function logActivity(User $user, ?int $clientId, string $action, string $description): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }

        ActivityLog::create([
            'user_id'     => $user->id,
            'client_id'   => $clientId,
            'action'      => $action,
            'module'      => 'seo',
            'description' => $description,
        ]);
    }
}
