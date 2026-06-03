<?php

namespace App\Console\Commands;

use App\Models\AiEmployee;
use App\Models\Permission;
use App\Models\ProspectActivity;
use App\Models\ProspectAiInsight;
use App\Models\ProspectLead;
use App\Models\ProspectMessageSuggestion;
use App\Models\ProspectPipeline;
use App\Models\ProspectStage;
use App\Services\AI\AIProviderManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DiagnoseProspectingCommand extends Command
{
    protected $signature = 'prospecting:diagnose';
    protected $description = 'Diagnóstico do módulo de Prospecção CRM com SDR IA';

    public function handle(): int
    {
        $this->info("=== DIAGNÓSTICO: Prospecção CRM / SDR IA ===\n");

        // Feature flags
        $this->section('Feature Flags');
        $this->checkFlag('prospecting_module');
        $this->checkFlag('crm_pipeline');
        $this->checkFlag('sdr_ai_assistant');
        $this->checkFlag('crm_auto_outreach', false);
        $this->checkFlag('crm_whatsapp_integration', false);
        $this->checkFlag('crm_scraping', false);

        // Permissions
        $this->section('Permissões');
        $perms = [
            'prospecting.view', 'prospecting.create', 'prospecting.update', 'prospecting.delete',
            'prospecting.pipeline.view', 'prospecting.pipeline.manage',
            'prospecting.activities.view', 'prospecting.activities.create', 'prospecting.activities.update',
            'prospecting.ai.analyze', 'prospecting.ai.generate_message',
            'prospecting.ai.suggest_next_action', 'prospecting.ai.qualify', 'prospecting.logs.view',
        ];
        foreach ($perms as $perm) {
            $exists = Permission::where('key', $perm)->exists();
            $this->line(($exists ? '  ✓' : '  ✗') . " {$perm}");
        }

        // Pipeline
        $this->section('Pipeline & Stages');
        $pipelines = ProspectPipeline::withCount('stages')->get();
        $this->line("  Pipelines: " . $pipelines->count());
        foreach ($pipelines as $p) {
            $this->line("    [{$p->id}] {$p->name} — {$p->stages_count} etapas" . ($p->is_default ? ' (padrão)' : ''));
        }

        $stageCount = ProspectStage::count();
        $this->line("  Total stages: {$stageCount}");

        // Leads
        $this->section('Leads');
        $total  = ProspectLead::count();
        $open   = ProspectLead::where('status', 'open')->count();
        $won    = ProspectLead::where('status', 'won')->count();
        $lost   = ProspectLead::where('status', 'lost')->count();
        $overdue = ProspectLead::where('status', 'open')->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<', now())->count();
        $this->line("  Total: {$total} (abertos: {$open}, fechados: {$won}, perdidos: {$lost})");
        $this->line("  Follow-ups atrasados: {$overdue}");

        // Activities
        $this->section('Atividades');
        $this->line("  Total: " . ProspectActivity::count());
        $this->line("  Pendentes: " . ProspectActivity::where('status', 'pending')->count());
        $this->line("  Atrasadas: " . ProspectActivity::overdue()->count());

        // AI
        $this->section('SDR IA');
        $sdr = AiEmployee::where('slug', 'sdr-ia')->first();
        $this->line("  SDR IA: " . ($sdr ? "✓ {$sdr->name} (status: {$sdr->status})" : "✗ Não encontrado"));
        $this->line("  Insights gerados: " . ProspectAiInsight::count());
        $this->line("  Mensagens geradas: " . ProspectMessageSuggestion::count());

        // AI Provider
        $this->section('Configuração de IA');
        try {
            $manager = app(AIProviderManager::class);
            $configured = $manager->isGeminiConfigured();
            $this->line("  Gemini configurado: " . ($configured ? '✓ Sim' : '✗ Não'));
            $this->line("  AI_PROVIDER: " . (config('ai.provider') ?: '(não definido)'));
        } catch (\Throwable $e) {
            $this->line("  Erro ao checar provider: " . $e->getMessage());
        }

        // Routes
        $this->section('Rotas registradas');
        $routes = collect(Route::getRoutes())->filter(fn($r) => str_contains($r->getName() ?? '', 'prospecting'));
        $this->line("  Total rotas prospecting: " . $routes->count());
        foreach ($routes as $r) {
            $this->line("    " . implode('|', $r->methods()) . " " . $r->uri() . " → " . $r->getName());
        }

        $this->newLine();
        $this->info("Diagnóstico concluído.");
        return 0;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan>── {$title}</>");
    }

    private function checkFlag(string $key, bool $expectedTrue = true): void
    {
        $value = (bool) config("features.{$key}");
        $ok = $expectedTrue ? $value : !$value;
        $symbol = $ok ? '✓' : '⚠';
        $this->line("  {$symbol} {$key}: " . ($value ? 'true' : 'false'));
    }
}
