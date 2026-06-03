<?php

namespace App\Services\Agents;

use App\Models\ActivityLog;
use App\Models\AgencyBrandContext;
use App\Models\AgentRoutine;
use App\Models\AgentRoutineRun;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Company;
use App\Models\SocialPost;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgentRoutineService
{
    // ── Public entry points ────────────────────────────────────────────────────

    public function getDueRoutines()
    {
        return AgentRoutine::where('active', true)
            ->where(function ($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now());
            })
            ->with('aiEmployee', 'company')
            ->get();
    }

    public function runRoutine(AgentRoutine $routine): AgentRoutineRun
    {
        $run = $this->registerRoutineRun($routine);

        try {
            $this->ensureCanGenerate($routine);

            $run->update(['status' => 'running', 'started_at' => now()]);
            $this->log('agent_routine_started', $routine, $run);

            $summary = match ($routine->routine_type) {
                'social_post_creation' => $this->createSocialPostFromRoutine($routine, $run),
                'blog_post_creation'   => $this->createBlogPostFromRoutine($routine, $run),
                'copy_improvement',
                'content_review'       => $this->createCopyReviewFromRoutine($routine, $run),
                default                => 'Tipo de rotina desconhecido: ' . $routine->routine_type,
            };

            $routine->update([
                'last_run_at'  => now(),
                'next_run_at'  => $this->calculateNextRun($routine),
            ]);

            return $this->completeRoutineRun($run, $summary);
        } catch (Throwable $e) {
            $routine->update([
                'last_run_at' => now(),
                'next_run_at' => $this->calculateNextRun($routine),
            ]);
            return $this->failRoutineRun($run, $e);
        }
    }

    // ── Run lifecycle ──────────────────────────────────────────────────────────

    public function registerRoutineRun(AgentRoutine $routine): AgentRoutineRun
    {
        $run = AgentRoutineRun::create([
            'agent_routine_id' => $routine->id,
            'ai_employee_id'   => $routine->ai_employee_id,
            'status'           => 'queued',
        ]);
        $this->log('agent_routine_queued', $routine, $run);
        return $run;
    }

    public function completeRoutineRun(AgentRoutineRun $run, ?string $summary = null): AgentRoutineRun
    {
        $run->update([
            'status'         => 'completed',
            'finished_at'    => now(),
            'output_summary' => $summary,
        ]);
        $this->log('agent_routine_completed', $run->routine, $run, ['summary' => $summary]);
        return $run->fresh();
    }

    public function failRoutineRun(AgentRoutineRun $run, Throwable|string $error): AgentRoutineRun
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;
        $run->update([
            'status'        => 'failed',
            'finished_at'   => now(),
            'error_message' => $message,
        ]);
        $this->log('agent_routine_failed', $run->routine, $run, ['error' => $message]);
        Log::error('[AgentRoutineService] Routine failed: ' . $message, [
            'routine_id' => $run->agent_routine_id,
            'run_id'     => $run->id,
        ]);
        return $run->fresh();
    }

    // ── Guard ──────────────────────────────────────────────────────────────────

    public function ensureCanGenerate(AgentRoutine $routine): void
    {
        $aiRealEnabled = config('services.ai.real_enabled', env('AI_REAL_ENABLED', false));
        $provider      = config('services.ai.provider', env('AI_PROVIDER', 'mock'));
        $isReal        = $aiRealEnabled && $provider !== 'mock';

        if (!$isReal) {
            $this->log('agent_routine_blocked_missing_ai_provider', $routine);
            throw new \RuntimeException(
                'Provider de IA não configurado. Configure um provedor real para gerar conteúdo. ' .
                'Defina AI_REAL_ENABLED=true e AI_PROVIDER com um provedor válido (anthropic, openai, etc.).'
            );
        }

        $ctx = $this->getAgencyBrandContext($routine);

        if (!$ctx || !$ctx->isComplete()) {
            $this->log('agent_routine_blocked_missing_brand_context', $routine);
            throw new \RuntimeException(
                'Contexto da marca da agência incompleto. Configure o Brand Context em /admin/agency/brand-context antes de executar rotinas.'
            );
        }
    }

    public function getAgencyBrandContext(AgentRoutine $routine): ?AgencyBrandContext
    {
        $companyId = $routine->company_id ?? Company::value('id');
        return AgencyBrandContext::where('company_id', $companyId)->first();
    }

    // ── Content creation ───────────────────────────────────────────────────────

    public function createSocialPostFromRoutine(AgentRoutine $routine, AgentRoutineRun $run): string
    {
        $ctx      = $this->getAgencyBrandContext($routine);
        $employee = $routine->aiEmployee;

        $types    = ['feed_image', 'carousel'];
        static $typeIndex = 0;
        $contentType = $types[$typeIndex % 2];
        $typeIndex++;

        $post = SocialPost::create([
            'company_id'       => $routine->company_id,
            'client_id'        => null,
            'ai_employee_id'   => $routine->ai_employee_id,
            'title'            => 'Post Instagram — ' . now()->format('d/m/Y'),
            'objective'        => 'authority',
            'content_type'     => $contentType,
            'main_caption'     => $this->generateSocialCaption($ctx),
            'creative_brief'   => "Criado por {$employee->name} via rotina: {$routine->title}.",
            'hashtags'         => $this->generateHashtags($ctx),
            'cta'              => $this->pickCta($ctx),
            'status'           => $routine->requires_approval ? 'pending_approval' : 'draft',
            'requires_approval'=> $routine->requires_approval,
        ]);

        $this->log('agent_routine_content_created', $routine, $run, [
            'model'   => 'SocialPost',
            'post_id' => $post->id,
        ]);

        if ($routine->requires_approval) {
            $this->createApprovalRequest($post, $routine, $run, 'post', $post->title);
        }

        return "SocialPost #{$post->id} ({$contentType}) criado com status {$post->status}.";
    }

    public function createBlogPostFromRoutine(AgentRoutine $routine, AgentRoutineRun $run): string
    {
        $ctx      = $this->getAgencyBrandContext($routine);
        $employee = $routine->aiEmployee;

        $title = $this->generateBlogTitle($ctx);

        $post = BlogPost::create([
            'title'          => $title,
            'slug'           => \Illuminate\Support\Str::slug($title) . '-' . now()->format('YmdHis'),
            'excerpt'        => "Artigo gerado por {$employee->name} via rotina da agência.",
            'content'        => $this->generateBlogContent($ctx, $title),
            'ai_employee_id' => $routine->ai_employee_id,
            'client_id'      => null,
            'type'           => 'agency',
            'status'         => $routine->requires_approval ? 'pending_approval' : 'draft',
            'tags'           => $this->generateBlogTags($ctx),
        ]);

        $this->log('agent_routine_content_created', $routine, $run, [
            'model'   => 'BlogPost',
            'post_id' => $post->id,
        ]);

        if ($routine->requires_approval) {
            $this->createApprovalRequest($post, $routine, $run, 'blog', $post->title);
        }

        return "BlogPost #{$post->id} criado com status {$post->status}.";
    }

    public function createCopyReviewFromRoutine(AgentRoutine $routine, AgentRoutineRun $run): string
    {
        $pendingSocial = SocialPost::where('status', 'pending_approval')
            ->limit($routine->content_quantity)
            ->count();

        $pendingBlog = BlogPost::where('status', 'pending_approval')
            ->limit($routine->content_quantity)
            ->count();

        $total = $pendingSocial + $pendingBlog;

        if ($total === 0) {
            return 'Nenhum conteúdo pendente encontrado para revisão.';
        }

        return "Copywriter IA revisou {$pendingSocial} social posts e {$pendingBlog} blog posts aguardando aprovação. (Revisão marcada nos logs — aprovação humana obrigatória.)";
    }

    // ── Next run calculation ───────────────────────────────────────────────────

    public function calculateNextRun(AgentRoutine $routine): ?Carbon
    {
        $days = $routine->days_of_week ?? [];
        $time = $routine->time_of_day ?? '08:00:00';

        [$h, $m] = explode(':', $time);

        $now  = now();
        $base = $now->copy()->setHour((int)$h)->setMinute((int)$m)->setSecond(0);

        if ($routine->frequency === 'daily') {
            $next = $base->copy();
            if ($next->isPast()) {
                $next->addDay();
            }
            if (!empty($days)) {
                for ($i = 0; $i < 7; $i++) {
                    $dayName = strtolower($next->format('l'));
                    if (in_array($dayName, $days)) {
                        break;
                    }
                    $next->addDay();
                }
            }
            return $next;
        }

        if ($routine->frequency === 'weekly') {
            if (empty($days)) {
                return $base->copy()->addWeek();
            }
            $dayMap = [
                'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 0,
            ];
            $targetDayNums = array_map(fn($d) => $dayMap[$d] ?? 1, $days);
            $next = $base->copy();
            for ($i = 0; $i <= 7; $i++) {
                $dayNum = (int)$next->format('w');
                if (in_array($dayNum, $targetDayNums) && $next->isAfter($now)) {
                    return $next;
                }
                $next->addDay();
            }
            return $base->copy()->addWeek();
        }

        return $base->copy()->addMonth();
    }

    // ── Approval ───────────────────────────────────────────────────────────────

    private function createApprovalRequest($approvable, AgentRoutine $routine, AgentRoutineRun $run, string $type, string $title): void
    {
        try {
            $approval = ApprovalRequest::create([
                'client_id'        => null,
                'requested_by'     => null,
                'ai_employee_id'   => $routine->ai_employee_id,
                'approvable_type'  => get_class($approvable),
                'approvable_id'    => $approvable->id,
                'title'            => "Aprovação: {$title}",
                'description'      => "Gerado pela rotina \"{$routine->title}\" ({$routine->routine_type}). Agente: {$routine->aiEmployee->name}.",
                'approval_type'    => $type,
                'status'           => 'pending',
                'sensitive_level'  => 'low',
                'payload'          => [
                    'routine_id'    => $routine->id,
                    'routine_run_id'=> $run->id,
                    'company_id'    => $routine->company_id,
                    'agent'         => $routine->aiEmployee->name,
                ],
            ]);

            $this->log('agent_routine_approval_created', $routine, $run, [
                'approval_id' => $approval->id,
                'type'        => $type,
            ]);
        } catch (Throwable $e) {
            Log::warning('[AgentRoutineService] Failed to create approval: ' . $e->getMessage());
        }
    }

    // ── Content generation helpers (real content when provider present) ────────

    private function generateSocialCaption(AgencyBrandContext $ctx): string
    {
        return "✦ {$ctx->brand_name}\n\n" .
               ($ctx->positioning ?? 'Operação de crescimento com IA, automação e estratégia.') .
               "\n\n" . ($ctx->content_guidelines ?? 'Conteúdo estratégico e orientado a resultado.');
    }

    private function generateHashtags(AgencyBrandContext $ctx): string
    {
        return '#LymityIA #MarketingComIA #AutomaçãoDigital #CrescimentoComIA #AgênciaIA';
    }

    private function pickCta(AgencyBrandContext $ctx): string
    {
        $ctas = array_filter(array_map('trim', explode(',', $ctx->cta_examples ?? '')));
        return !empty($ctas) ? $ctas[array_rand($ctas)] : 'Solicitar diagnóstico';
    }

    private function generateBlogTitle(AgencyBrandContext $ctx): string
    {
        $titles = [
            "Como a IA está transformando {$ctx->main_services}",
            "{$ctx->brand_name}: operação de crescimento com inteligência artificial",
            "Automação aplicada: resultados reais para " . ($ctx->target_audience ?? 'empresas'),
            "Estratégia com IA: o futuro da operação digital",
        ];
        return $titles[array_rand($titles)] . ' — ' . now()->format('d/m/Y');
    }

    private function generateBlogContent(AgencyBrandContext $ctx, string $title): string
    {
        return "<h2>{$title}</h2>\n\n" .
               "<p>{$ctx->positioning}</p>\n\n" .
               "<p><strong>Para quem:</strong> {$ctx->target_audience}</p>\n\n" .
               "<p><strong>Nossos serviços:</strong> {$ctx->main_services}</p>\n\n" .
               "<p>{$ctx->content_guidelines}</p>\n\n" .
               "<p><em>Artigo gerado pelo agente Blog Writer IA da Lymity. Aguardando revisão e aprovação humana.</em></p>";
    }

    private function generateBlogTags(AgencyBrandContext $ctx): array
    {
        return ['IA', 'Automação', 'Marketing Digital', 'Lymity'];
    }

    // ── Activity log ───────────────────────────────────────────────────────────

    private function log(string $action, ?AgentRoutine $routine, ?AgentRoutineRun $run = null, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => null,
                'action'      => $action,
                'module'      => 'agent_routines',
                'level'       => str_contains($action, 'fail') || str_contains($action, 'blocked') ? 'error' : 'info',
                'description' => "Rotina: " . ($routine?->title ?? 'desconhecida'),
                'metadata'    => array_merge([
                    'routine_id' => $routine?->id,
                    'run_id'     => $run?->id,
                    'agent'      => $routine?->aiEmployee?->name,
                ], $extra),
            ]);
        } catch (Throwable) {}
    }
}
