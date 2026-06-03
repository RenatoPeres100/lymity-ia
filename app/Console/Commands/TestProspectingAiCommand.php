<?php

namespace App\Console\Commands;

use App\Models\ProspectLead;
use App\Models\User;
use App\Services\Prospecting\ProspectingAiService;
use Illuminate\Console\Command;

class TestProspectingAiCommand extends Command
{
    protected $signature = 'prospecting:test-ai {type=analyze}';
    protected $description = 'Testa SDR IA com lead existente';

    public function handle(ProspectingAiService $aiService): int
    {
        $user = User::where('role', 'admin_geral')->firstOrFail();
        $lead = ProspectLead::firstOrFail();
        $type = $this->argument('type');

        $this->info("Lead: {$lead->name} ({$lead->company_name})");
        $this->info("Tipo de teste: {$type}");

        try {
            switch ($type) {
                case 'analyze':
                    $result = $aiService->analyzeLead($lead, $user);
                    $this->info("Insight criado: ID={$result->id}");
                    $this->info("Title: {$result->title}");
                    $this->line("Content: " . substr($result->content, 0, 300));
                    break;
                case 'qualify':
                    $result = $aiService->qualifyLead($lead, $user);
                    $this->info("Qualificação criada: ID={$result->id}, Score={$result->score}");
                    $this->line("Content: " . substr($result->content, 0, 300));
                    break;
                case 'message':
                    $result = $aiService->generateMessage($lead, 'whatsapp', 'first_contact', $user);
                    $this->info("Mensagem criada: ID={$result->id}");
                    $this->line("Mensagem: " . substr($result->message, 0, 500));
                    break;
                case 'next-action':
                    $result = $aiService->suggestNextAction($lead, $user);
                    $this->info("Sugestão criada: ID={$result->id}");
                    $this->line("Ação: " . substr($result->content, 0, 300));
                    break;
                case 'summarize':
                    $result = $aiService->summarizeHistory($lead, $user);
                    $this->info("Resumo criado: ID={$result->id}");
                    $this->line("Resumo: " . substr($result->content, 0, 300));
                    break;
            }

            $this->newLine();
            $this->info("ProspectAiInsight::count() = " . \App\Models\ProspectAiInsight::count());
            $this->info("ProspectMessageSuggestion::count() = " . \App\Models\ProspectMessageSuggestion::count());
            return 0;
        } catch (\Throwable $e) {
            $this->error("Erro: " . $e->getMessage());
            return 1;
        }
    }
}
