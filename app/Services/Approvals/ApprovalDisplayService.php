<?php

namespace App\Services\Approvals;

use App\Models\AgentTask;
use App\Models\AgentTaskRun;
use App\Models\AiEmployee;
use App\Models\AiExecutionContext;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\GeneratedContentPackage;
use App\Models\SocialPost;
use Illuminate\Database\Eloquent\Model;

class ApprovalDisplayService
{
    public function build(ApprovalRequest $approval): array
    {
        $package = $this->resolveGeneratedPackage($approval);
        $entity  = $this->resolveGeneratedEntity($approval, $package);
        $run     = $this->resolveAgentTaskRun($package, $approval);
        $context = $this->resolveExecutionContext($package, $run);
        $task    = $this->resolveAgentTask($package, $run, $context);
        $employee = $this->resolveEmployee($approval, $package, $run);

        return [
            'approval'  => $approval,
            'package'   => $package,
            'entity'    => $entity,
            'task'      => $task,
            'employee'  => $employee,
            'run'       => $run,
            'context'   => $context,
            'content'   => $this->formatContentPayload($package, $entity),
            'visual'    => $this->formatVisualPayload($package, $entity),
            'sources'   => $this->formatSources($package, $context),
            'ctx_data'  => $this->formatContext($context, $task),
            'usage'     => $this->formatUsage($run, $context),
            'debug'     => $this->formatRawDebug($approval, $package, $context),
        ];
    }

    public function resolveGeneratedPackage(ApprovalRequest $approval): ?GeneratedContentPackage
    {
        // 1. approvable is directly the package
        if ($approval->approvable_type === GeneratedContentPackage::class ||
            str_ends_with((string)$approval->approvable_type, 'GeneratedContentPackage')) {
            $pkg = $approval->approvable;
            if ($pkg instanceof GeneratedContentPackage) {
                return $pkg->load(['agentTask', 'agentTaskRun', 'aiEmployee', 'assets']);
            }
        }

        // 2. payload references
        $payload = $approval->payload ?? [];
        $pkgId = $payload['generated_content_package_id']
            ?? $payload['package_id']
            ?? null;

        if ($pkgId) {
            return GeneratedContentPackage::with(['agentTask', 'agentTaskRun', 'aiEmployee', 'assets'])
                ->find($pkgId);
        }

        // 3. approvable is BlogPost/SocialPost → find package by entity
        if ($approval->approvable_id && $approval->approvable_type) {
            $pkg = GeneratedContentPackage::with(['agentTask', 'agentTaskRun', 'aiEmployee', 'assets'])
                ->where('generated_entity_type', $approval->approvable_type)
                ->where('generated_entity_id', $approval->approvable_id)
                ->latest()
                ->first();
            if ($pkg) return $pkg;
        }

        // 4. by approval_request_id on package table
        $pkg = GeneratedContentPackage::with(['agentTask', 'agentTaskRun', 'aiEmployee', 'assets'])
            ->where('approval_request_id', $approval->id)
            ->latest()
            ->first();

        return $pkg;
    }

    public function resolveGeneratedEntity(ApprovalRequest $approval, ?GeneratedContentPackage $package): ?Model
    {
        // from package
        if ($package && $package->generated_entity_type && $package->generated_entity_id) {
            try {
                return $package->generatedEntity;
            } catch (\Throwable) {}
        }

        // from approvable directly
        if ($approval->approvable_type && $approval->approvable_id) {
            $type = $approval->approvable_type;
            if (str_ends_with($type, 'BlogPost') || str_ends_with($type, 'SocialPost')) {
                return $approval->approvable;
            }
        }

        // from payload
        $payload = $approval->payload ?? [];
        $entityType = $payload['entity_type'] ?? null;
        $entityId   = $payload['entity_id'] ?? null;

        if ($entityType && $entityId) {
            try {
                return $entityType::find($entityId);
            } catch (\Throwable) {}
        }

        return null;
    }

    public function resolveAgentTaskRun(?GeneratedContentPackage $package, ApprovalRequest $approval): ?AgentTaskRun
    {
        if ($package?->agent_task_run_id) {
            return AgentTaskRun::find($package->agent_task_run_id);
        }

        $payload = $approval->payload ?? [];
        $runId = $payload['agent_task_run_id'] ?? $payload['task_run_id'] ?? null;
        if ($runId) {
            return AgentTaskRun::find($runId);
        }

        return null;
    }

    public function resolveExecutionContext(?GeneratedContentPackage $package, ?AgentTaskRun $run): ?AiExecutionContext
    {
        if ($run?->execution_context_id) {
            return AiExecutionContext::find($run->execution_context_id);
        }
        return null;
    }

    private function resolveAgentTask(?GeneratedContentPackage $package, ?AgentTaskRun $run, ?AiExecutionContext $context): ?AgentTask
    {
        if ($package?->agent_task_id) {
            return AgentTask::find($package->agent_task_id);
        }
        if ($run?->agent_task_id) {
            return AgentTask::find($run->agent_task_id);
        }
        if ($context?->agent_task_id) {
            return AgentTask::find($context->agent_task_id);
        }
        return null;
    }

    private function resolveEmployee(ApprovalRequest $approval, ?GeneratedContentPackage $package, ?AgentTaskRun $run): ?AiEmployee
    {
        if ($approval->ai_employee_id) {
            return $approval->aiEmployee ?? AiEmployee::find($approval->ai_employee_id);
        }
        if ($package?->ai_employee_id) {
            return $package->aiEmployee ?? AiEmployee::find($package->ai_employee_id);
        }
        if ($run?->ai_employee_id) {
            return AiEmployee::find($run->ai_employee_id);
        }
        return null;
    }

    public function formatContentPayload(?GeneratedContentPackage $package, ?Model $entity = null): array
    {
        $cp   = $package?->content_payload ?? [];
        $type = $package?->package_type ?? 'unknown';

        // Try to enrich from the entity model
        if ($entity instanceof BlogPost) {
            return $this->formatBlogContent($cp, $entity);
        }
        if ($entity instanceof SocialPost) {
            return $this->formatSocialContent($cp, $entity);
        }

        // Fallback: infer from package_type or cp keys
        if ($type === 'blog_post' || isset($cp['content_html']) || isset($cp['focus_keyword'])) {
            return $this->formatBlogContent($cp, null);
        }
        if ($type === 'social_post' || $type === 'carousel' || isset($cp['caption']) || isset($cp['hashtags'])) {
            return $this->formatSocialContent($cp, null);
        }

        return [
            'type'    => $type,
            'missing' => empty($cp),
            'raw'     => $cp,
        ];
    }

    private function formatBlogContent(array $cp, ?BlogPost $entity): array
    {
        $title            = $entity?->title           ?? $cp['title']           ?? null;
        $slug             = $entity?->slug            ?? $cp['slug']            ?? null;
        $excerpt          = $entity?->excerpt         ?? $cp['excerpt']         ?? null;
        $seoTitle         = $entity?->seo_title       ?? $cp['seo_title']       ?? null;
        $seoDescription   = $entity?->seo_description ?? $cp['seo_description'] ?? null;
        $focusKeyword     = $entity?->focus_keyword   ?? $cp['focus_keyword']   ?? null;
        $secondaryKws     = $entity?->secondary_keywords ?? $cp['secondary_keywords'] ?? [];
        $contentHtml      = $entity?->content         ?? $cp['content_html']    ?? null;
        $contentMarkdown  = $cp['content_markdown']   ?? null;
        $ctaFinal         = $cp['cta_final']          ?? null;
        $imagePrompt      = $cp['image_prompt']       ?? null;
        $sourcesUsed      = $cp['sources_used']       ?? [];

        // ensure array
        if (is_string($secondaryKws)) {
            $secondaryKws = array_filter(array_map('trim', explode(',', $secondaryKws)));
        }

        return [
            'type'              => 'blog_post',
            'title'             => $title,
            'slug'              => $slug,
            'excerpt'           => $excerpt,
            'seo_title'         => $seoTitle,
            'seo_description'   => $seoDescription,
            'focus_keyword'     => $focusKeyword,
            'secondary_keywords'=> (array)$secondaryKws,
            'content_html'      => $contentHtml,
            'content_markdown'  => $contentMarkdown,
            'cta_final'         => $ctaFinal,
            'image_prompt'      => $imagePrompt,
            'sources_used'      => (array)$sourcesUsed,
            'missing'           => empty($title) && empty($contentHtml),
            'entity'            => $entity,
        ];
    }

    private function formatSocialContent(array $cp, ?SocialPost $entity): array
    {
        $type         = $cp['content_type'] ?? 'social_post';
        $title        = $entity?->title       ?? $cp['title']          ?? null;
        $caption      = $entity?->caption     ?? $cp['caption']        ?? $cp['body'] ?? null;
        $hashtags     = $entity?->hashtags    ?? $cp['hashtags']       ?? [];
        $cta          = $cp['cta']            ?? null;
        $brief        = $cp['creative_brief'] ?? null;
        $imagePrompt  = $cp['image_prompt']   ?? null;
        $slides       = $cp['slides']         ?? [];

        if (is_string($hashtags)) {
            $hashtags = array_filter(array_map('trim', explode(' ', $hashtags)));
        }

        return [
            'type'          => $type,
            'title'         => $title,
            'caption'       => $caption,
            'hashtags'      => (array)$hashtags,
            'cta'           => $cta,
            'creative_brief'=> $brief,
            'image_prompt'  => $imagePrompt,
            'slides'        => (array)$slides,
            'missing'       => empty($title) && empty($caption),
            'entity'        => $entity,
        ];
    }

    public function formatVisualPayload(?GeneratedContentPackage $package, ?Model $entity = null): array
    {
        $vp     = $package?->visual_payload ?? [];
        $assets = $package?->assets ?? collect();

        $featuredUrl = $vp['featured_image_url'] ?? $vp['feed_image_url']
            ?? ($entity instanceof BlogPost ? $entity->featured_image : null)
            ?? ($entity instanceof SocialPost ? $entity->public_image_url : null)
            ?? null;

        $visualStatus  = $vp['visual_status'] ?? 'unknown';
        $imagePrompt   = $vp['image_prompt']  ?? null;
        $imageType     = $vp['image_type']    ?? null;

        $formattedAssets = $assets->map(function ($asset) {
            return [
                'id'          => $asset->id,
                'type'        => $asset->asset_type,
                'slide_order' => $asset->slide_order,
                'prompt'      => $asset->prompt,
                'public_url'  => $asset->public_url,
                'local_path'  => $asset->local_path,
                'status'      => $asset->status,
                'status_label'=> $asset->status_label,
                'error'       => $asset->error_message,
                'is_generated'=> $asset->isGenerated(),
            ];
        })->values()->all();

        return [
            'featured_url'  => $featuredUrl,
            'visual_status' => $visualStatus,
            'image_prompt'  => $imagePrompt,
            'image_type'    => $imageType,
            'assets'        => $formattedAssets,
            'has_image'     => !empty($featuredUrl) || collect($formattedAssets)->where('is_generated', true)->isNotEmpty(),
            'carousel_data' => ($entity instanceof SocialPost) ? ($entity->carousel_data ?? null) : null,
        ];
    }

    public function formatSources(?GeneratedContentPackage $package, ?AiExecutionContext $context): array
    {
        $sp       = $package?->sources_payload ?? [];
        $research = $context?->external_research_snapshot ?? [];

        $sources = [];

        if (!empty($sp['sources'])) {
            $sources = (array)$sp['sources'];
        } elseif (!empty($research['sources'])) {
            $sources = (array)$research['sources'];
        } elseif (!empty($research)) {
            $sources = [$research];
        }

        return [
            'items'       => $sources,
            'has_sources' => !empty($sources),
            'raw_research'=> $research,
        ];
    }

    public function formatContext(?AiExecutionContext $context, ?AgentTask $task): array
    {
        if (!$context && !$task) {
            return ['missing' => true];
        }

        $memories = collect($context?->selected_memories ?? [])
            ->take(5)
            ->map(fn($m) => [
                'id'      => $m['id'] ?? null,
                'type'    => $m['type'] ?? '—',
                'title'   => $m['title'] ?? '—',
                'content' => isset($m['content']) ? \Str::limit($m['content'], 200) : null,
            ])->values()->all();

        $promptPreview = null;
        if ($context?->prompt_preview) {
            // Sanitize: remove anything that looks like a key/secret
            $preview = $context->prompt_preview;
            $preview = preg_replace('/\b(sk-|AIza|Bearer )\S+/i', '[REDACTED]', $preview);
            $promptPreview = \Str::limit($preview, 2000);
        }

        return [
            'missing'               => false,
            'brand_context_id'      => $context?->brand_context_id,
            'brand_context_version' => $context?->brand_context_version,
            'brand_context_hash'    => $context?->brand_context_hash,
            'compact_brand_context' => $context?->compact_brand_context,
            'compact_task_context'  => $context?->compact_task_context ?? $task?->compact_task_context,
            'task_title'            => $task?->title,
            'task_type'             => $task?->task_type,
            'task_channel'          => $task?->content_channel,
            'task_format'           => $task?->content_format,
            'task_instructions'     => $task?->operational_instructions,
            'memories'              => $memories,
            'prompt_preview'        => $promptPreview,
        ];
    }

    public function formatUsage(?AgentTaskRun $run, ?AiExecutionContext $context): array
    {
        return [
            'run_id'           => $run?->id,
            'status'           => $run?->status,
            'status_label'     => $run?->status_label,
            'status_color'     => $run?->status_color,
            'provider'         => $context?->provider ?? $run?->aiEmployee?->provider_type ?? null,
            'model'            => $context?->model    ?? null,
            'input_tokens'     => $run?->input_tokens     ?? $context?->input_tokens_estimated  ?? null,
            'output_tokens'    => $run?->output_tokens    ?? $context?->output_tokens_estimated ?? null,
            'estimated_cost'   => $run?->estimated_cost_usd ?? $context?->estimated_cost_usd   ?? null,
            'duration'         => $run?->duration,
            'started_at'       => $run?->started_at,
            'finished_at'      => $run?->finished_at,
            'error_message'    => $run?->error_message,
        ];
    }

    public function formatRawDebug(ApprovalRequest $approval, ?GeneratedContentPackage $package, ?AiExecutionContext $context): array
    {
        return [
            'approval_payload'   => $approval->payload,
            'content_payload'    => $package?->content_payload,
            'visual_payload'     => $package?->visual_payload,
            'sources_payload'    => $package?->sources_payload,
            'context_snapshot'   => $context ? [
                'id'                  => $context->id,
                'provider'            => $context->provider,
                'model'               => $context->model,
                'brand_context_id'    => $context->brand_context_id,
                'brand_context_hash'  => $context->brand_context_hash,
                'task_context_hash'   => null,
                'prompt_hash'         => $context->prompt_hash,
                'status'              => $context->status,
            ] : null,
        ];
    }
}
