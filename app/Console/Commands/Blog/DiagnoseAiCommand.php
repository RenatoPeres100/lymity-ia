<?php

namespace App\Console\Commands\Blog;

use App\Models\AgencyBrandContext;
use App\Models\AiEmployee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DiagnoseAiCommand extends Command
{
    protected $signature   = 'blog:diagnose-ai';
    protected $description = 'Diagnóstico completo do pipeline de blog com Gemini.';

    public function handle(): int
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════');
        $this->info('  DIAGNÓSTICO — Blog Automation (Gemini Text Only)');
        $this->line('═══════════════════════════════════════════════════');

        $ok      = '<fg=green>  OK</>';
        $warn    = '<fg=yellow>  WARNING</>';
        $error   = '<fg=red>  ERROR</>';
        $allGood = true;

        // AI_PROVIDER
        $provider = config('ai.provider', '');
        if ($provider === 'google') {
            $this->line("{$ok}  AI_PROVIDER=google");
        } else {
            $this->line("{$error}  AI_PROVIDER={$provider} (esperado: google)");
            $allGood = false;
        }

        // GEMINI_API_KEY
        $apiKey = config('ai.gemini_api_key', '');
        if (!empty($apiKey)) {
            $this->line("{$ok}  GEMINI_API_KEY configurado (não exibido)");
        } else {
            $this->line("{$error}  GEMINI_API_KEY não configurado");
            $allGood = false;
        }

        // GEMINI_TEXT_MODEL
        $model = config('ai.gemini_text_model', '');
        if (!empty($model)) {
            $this->line("{$ok}  GEMINI_TEXT_MODEL={$model}");
        } else {
            $this->line("{$warn}  GEMINI_TEXT_MODEL não definido (usando padrão)");
        }

        // GEMINI_TEXT_FALLBACK_MODEL
        $fallback = config('ai.gemini_text_fallback_model', '');
        if (!empty($fallback)) {
            $this->line("{$ok}  GEMINI_TEXT_FALLBACK_MODEL={$fallback}");
        } else {
            $this->line("{$warn}  GEMINI_TEXT_FALLBACK_MODEL não definido");
        }

        // AI_TEXT_ONLY_MODE
        $textOnly = config('ai.text_only_mode', false);
        $textOnly
            ? $this->line("{$ok}  AI_TEXT_ONLY_MODE=true")
            : $this->line("{$warn}  AI_TEXT_ONLY_MODE=false (recomendado: true)");

        // Image pipeline
        $imagePipeline = config('features.blog_image_pipeline', true);
        !$imagePipeline
            ? $this->line("{$ok}  blog_image_pipeline=false (desativado)")
            : $this->line("{$warn}  blog_image_pipeline=true (deveria estar desativado)");

        // Instagram pipeline
        $igPipeline = config('features.instagram_pipeline', true);
        !$igPipeline
            ? $this->line("{$ok}  instagram_pipeline=false (desativado)")
            : $this->line("{$warn}  instagram_pipeline=true (deveria estar desativado)");

        // Limits
        $dailyLimit    = config('ai.daily_request_limit', 0);
        $monthlyBudget = config('ai.monthly_budget_usd', 0);
        $this->line("{$ok}  AI_DAILY_REQUEST_LIMIT={$dailyLimit}");
        $this->line("{$ok}  AI_MONTHLY_BUDGET_USD={$monthlyBudget}");

        // BrandContext
        $ctx = AgencyBrandContext::where('active', true)->whereNull('client_id')->latest()->first();
        if ($ctx && $ctx->isComplete()) {
            $this->line("{$ok}  BrandContext ativo: {$ctx->brand_name}");
        } elseif ($ctx) {
            $this->line("{$warn}  BrandContext existe mas está incompleto (faltam campos obrigatórios)");
        } else {
            $this->line("{$error}  BrandContext não configurado. Acesse /admin/agency/brand-context");
            $allGood = false;
        }

        // Blog Planner IA
        $planner = AiEmployee::where('slug', 'blog-planner-ia')->where('status', 'active')->first();
        if ($planner) {
            $this->line("{$ok}  Blog Planner IA ativo (ID: {$planner->id})");
        } else {
            $this->line("{$error}  Blog Planner IA não encontrado ou inativo");
            $allGood = false;
        }

        // Blog Writer IA
        $writer = AiEmployee::where('slug', 'blog-writer-ia')->where('status', 'active')->first();
        if ($writer) {
            $this->line("{$ok}  Blog Writer IA ativo (ID: {$writer->id})");
        } else {
            $this->line("{$error}  Blog Writer IA não encontrado ou inativo");
            $allGood = false;
        }

        // blog:publish-due command
        $commands = Artisan::all();
        isset($commands['blog:publish-due'])
            ? $this->line("{$ok}  Comando blog:publish-due registrado")
            : $this->line("{$error}  Comando blog:publish-due não encontrado");

        // Queue
        $queueDriver = config('queue.default', 'sync');
        $this->line("{$ok}  Queue driver: {$queueDriver}");

        $this->line('');
        if ($allGood) {
            $this->info('  DIAGNÓSTICO CONCLUÍDO: Sistema pronto para gerar conteúdo com Gemini.');
        } else {
            $this->warn('  DIAGNÓSTICO CONCLUÍDO: Há erros que precisam ser corrigidos antes de usar o pipeline.');
        }
        $this->line('');

        return $allGood ? self::SUCCESS : self::FAILURE;
    }
}
