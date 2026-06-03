<?php

namespace App\Services\Prospecting;

use App\Models\AiEmployee;
use App\Models\ProspectAiInsight;
use App\Models\ProspectLead;
use App\Models\ProspectMessageSuggestion;
use App\Models\User;
use App\Services\AI\AICostGuardService;
use App\Services\AI\AIProviderManager;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ProspectingAiService
{
    public function __construct(
        private AIProviderManager   $providerManager,
        private AICostGuardService  $costGuard,
        private ProspectingLogService $logger,
    ) {}

    public function analyzeLead(ProspectLead $lead, User $user): ProspectAiInsight
    {
        $this->costGuard->assertCanGenerate('prospecting.analyze');

        $sdrEmployee = $this->getSdrEmployee();
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage  = $this->buildLeadContext($lead)
            . "\n\nTarefa: Analise este lead e retorne um JSON com a estrutura:\n"
            . json_encode([
                'title'                  => 'string',
                'summary'                => 'string',
                'detected_opportunities' => ['string'],
                'possible_pain_points'   => ['string'],
                'recommended_offer'      => 'string',
                'risk_points'            => ['string'],
                'next_best_action'       => 'string',
            ], JSON_UNESCAPED_UNICODE);

        $response = $this->providerManager->provider()->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => 2000,
        ]);

        if (!$response->success) {
            throw new RuntimeException("IA não disponível: " . $response->error_message);
        }

        $payload = $response->json ?? [];
        $title   = $payload['title'] ?? "Análise do lead {$lead->name}";
        $content = $payload['summary'] ?? '';

        $insight = ProspectAiInsight::create([
            'prospect_lead_id'   => $lead->id,
            'company_id'         => $lead->company_id,
            'ai_employee_id'     => $sdrEmployee?->id,
            'insight_type'       => 'analysis',
            'title'              => $title,
            'content'            => $content,
            'payload'            => $payload,
            'model'              => $response->model,
            'provider'           => $response->provider,
            'input_tokens'       => $response->input_tokens,
            'output_tokens'      => $response->output_tokens,
            'estimated_cost_usd' => $response->estimated_cost_usd,
        ]);

        $this->logger->log('prospect.ai.analyzed', $insight, $user, ['lead' => $lead->name]);

        return $insight;
    }

    public function qualifyLead(ProspectLead $lead, User $user): ProspectAiInsight
    {
        $this->costGuard->assertCanGenerate('prospecting.qualify');

        $sdrEmployee = $this->getSdrEmployee();
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage  = $this->buildLeadContext($lead)
            . "\n\nTarefa: Qualifique este lead e retorne um JSON com a estrutura:\n"
            . json_encode([
                'fit_score'              => 0,
                'interest_level'         => 'cold|warm|hot',
                'qualification_summary'  => 'string',
                'why_this_score'         => 'string',
                'recommended_stage'      => 'string',
            ], JSON_UNESCAPED_UNICODE);

        $response = $this->providerManager->provider()->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => 1500,
        ]);

        if (!$response->success) {
            throw new RuntimeException("IA não disponível: " . $response->error_message);
        }

        $payload = $response->json ?? [];
        $score   = isset($payload['fit_score']) ? (int) $payload['fit_score'] : null;

        $insight = ProspectAiInsight::create([
            'prospect_lead_id'   => $lead->id,
            'company_id'         => $lead->company_id,
            'ai_employee_id'     => $sdrEmployee?->id,
            'insight_type'       => 'qualification',
            'title'              => "Qualificação do lead {$lead->name}",
            'content'            => $payload['qualification_summary'] ?? '',
            'score'              => $score,
            'payload'            => $payload,
            'model'              => $response->model,
            'provider'           => $response->provider,
            'input_tokens'       => $response->input_tokens,
            'output_tokens'      => $response->output_tokens,
            'estimated_cost_usd' => $response->estimated_cost_usd,
        ]);

        $this->logger->log('prospect.ai.qualified', $insight, $user, [
            'lead'      => $lead->name,
            'fit_score' => $score,
        ]);

        return $insight;
    }

    public function generateMessage(ProspectLead $lead, string $channel, string $objective, User $user): ProspectMessageSuggestion
    {
        $this->costGuard->assertCanGenerate('prospecting.generate_message');

        $sdrEmployee = $this->getSdrEmployee();
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage  = $this->buildLeadContext($lead)
            . "\n\nTarefa: Crie uma mensagem personalizada para este lead.\n"
            . "Canal: {$channel}\n"
            . "Objetivo: {$objective}\n\n"
            . "Retorne um JSON com a estrutura:\n"
            . json_encode([
                'channel'   => $channel,
                'objective' => $objective,
                'message'   => 'string',
                'tone'      => 'string',
                'notes'     => 'string',
            ], JSON_UNESCAPED_UNICODE);

        $response = $this->providerManager->provider()->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => 1500,
        ]);

        if (!$response->success) {
            throw new RuntimeException("IA não disponível: " . $response->error_message);
        }

        $payload = $response->json ?? [];

        $suggestion = ProspectMessageSuggestion::create([
            'prospect_lead_id' => $lead->id,
            'company_id'       => $lead->company_id,
            'ai_employee_id'   => $sdrEmployee?->id,
            'channel'          => $channel,
            'objective'        => $objective,
            'message'          => $payload['message'] ?? '',
            'status'           => 'draft',
            'metadata'         => array_filter([
                'tone'     => $payload['tone'] ?? null,
                'notes'    => $payload['notes'] ?? null,
                'model'    => $response->model,
                'provider' => $response->provider,
            ]),
        ]);

        $this->logger->log('prospect.ai.message_generated', $suggestion, $user, [
            'lead'      => $lead->name,
            'channel'   => $channel,
            'objective' => $objective,
        ]);

        return $suggestion;
    }

    public function suggestNextAction(ProspectLead $lead, User $user): ProspectAiInsight
    {
        $this->costGuard->assertCanGenerate('prospecting.suggest_next_action');

        $sdrEmployee = $this->getSdrEmployee();
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage  = $this->buildLeadContext($lead)
            . "\n\nTarefa: Sugira a próxima ação mais eficaz para avançar este lead. Retorne um JSON com a estrutura:\n"
            . json_encode([
                'next_action'      => 'string',
                'reason'           => 'string',
                'suggested_due_date' => 'YYYY-MM-DD|null',
                'priority'         => 'low|medium|high',
            ], JSON_UNESCAPED_UNICODE);

        $response = $this->providerManager->provider()->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => 1000,
        ]);

        if (!$response->success) {
            throw new RuntimeException("IA não disponível: " . $response->error_message);
        }

        $payload = $response->json ?? [];

        $insight = ProspectAiInsight::create([
            'prospect_lead_id'   => $lead->id,
            'company_id'         => $lead->company_id,
            'ai_employee_id'     => $sdrEmployee?->id,
            'insight_type'       => 'next_action',
            'title'              => "Próxima ação sugerida para {$lead->name}",
            'content'            => $payload['next_action'] ?? '',
            'payload'            => $payload,
            'model'              => $response->model,
            'provider'           => $response->provider,
            'input_tokens'       => $response->input_tokens,
            'output_tokens'      => $response->output_tokens,
            'estimated_cost_usd' => $response->estimated_cost_usd,
        ]);

        $this->logger->log('prospect.ai.next_action_suggested', $insight, $user, ['lead' => $lead->name]);

        return $insight;
    }

    public function summarizeHistory(ProspectLead $lead, User $user): ProspectAiInsight
    {
        $this->costGuard->assertCanGenerate('prospecting.summarize');

        $sdrEmployee = $this->getSdrEmployee();
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage  = $this->buildLeadContext($lead)
            . "\n\nTarefa: Resuma o histórico deste lead e forneça uma visão geral do status atual. Retorne um JSON com a estrutura:\n"
            . json_encode([
                'summary'               => 'string',
                'important_events'      => ['string'],
                'current_status'        => 'string',
                'recommended_next_step' => 'string',
            ], JSON_UNESCAPED_UNICODE);

        $response = $this->providerManager->provider()->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => 2000,
        ]);

        if (!$response->success) {
            throw new RuntimeException("IA não disponível: " . $response->error_message);
        }

        $payload = $response->json ?? [];

        $insight = ProspectAiInsight::create([
            'prospect_lead_id'   => $lead->id,
            'company_id'         => $lead->company_id,
            'ai_employee_id'     => $sdrEmployee?->id,
            'insight_type'       => 'summary',
            'title'              => "Resumo do histórico de {$lead->name}",
            'content'            => $payload['summary'] ?? '',
            'payload'            => $payload,
            'model'              => $response->model,
            'provider'           => $response->provider,
            'input_tokens'       => $response->input_tokens,
            'output_tokens'      => $response->output_tokens,
            'estimated_cost_usd' => $response->estimated_cost_usd,
        ]);

        $this->logger->log('prospect.ai.history_summarized', $insight, $user, ['lead' => $lead->name]);

        return $insight;
    }

    private function getSdrEmployee(): ?AiEmployee
    {
        return AiEmployee::where('slug', 'sdr-ia')->first();
    }

    private function buildSystemPrompt(): string
    {
        return "Você é o SDR IA da Lymity IA. Sua função é ajudar a equipe comercial a analisar leads, "
            . "identificar oportunidades, sugerir abordagens, criar mensagens personalizadas e recomendar próximos passos. "
            . "Você não envia mensagens automaticamente. Você não promete resultados garantidos. "
            . "Você escreve com clareza, educação, estratégia e objetividade. "
            . "Sempre personalize a análise com base nos dados disponíveis do lead.\n\n"
            . "Contexto da Lymity IA: empresa de tecnologia aplicada ao crescimento digital. "
            . "Une IA, automação, desenvolvimento, conteúdo, SEO, social media e estratégia digital. "
            . "Foco em operação real, crescimento e inteligência. "
            . "Abordagem consultiva e personalizada. Não vende promessas irreais.\n\n"
            . "IMPORTANTE: Retorne SEMPRE um JSON válido, sem markdown, sem código, apenas o objeto JSON puro.";
    }

    private function buildLeadContext(ProspectLead $lead): string
    {
        $lead->load(['stage', 'pipeline', 'activities' => fn($q) => $q->latest()->limit(5), 'noteEntries' => fn($q) => $q->latest()->limit(3)]);

        $activities = $lead->activities->map(fn($a) =>
            "[{$a->created_at->format('d/m/Y')}] {$a->type_label}: {$a->title}" . ($a->outcome ? " — {$a->outcome}" : '')
        )->implode("\n");

        $notes = $lead->noteEntries->map(fn($n) =>
            "[{$n->created_at->format('d/m/Y')}] {$n->note}"
        )->implode("\n");

        $parts = [
            "=== DADOS DO LEAD ===",
            "Nome: {$lead->name}",
            "Empresa: " . ($lead->company_name ?: '(não informado)'),
            "Segmento: " . ($lead->segment ?: '(não informado)'),
            "Website: " . ($lead->website ?: '(não informado)'),
            "Instagram: " . ($lead->instagram ?: '(não informado)'),
            "LinkedIn: " . ($lead->linkedin ?: '(não informado)'),
            "WhatsApp: " . ($lead->whatsapp ?: '(não informado)'),
            "E-mail: " . ($lead->email ?: '(não informado)'),
            "Cidade/Estado: " . (trim(($lead->city ?: '') . '/' . ($lead->state ?: ''), '/') ?: '(não informado)'),
            "Origem: " . ($lead->source ?: '(não informado)'),
            "Etapa atual: " . ($lead->stage?->name ?: '(não informado)'),
            "Interesse: " . $lead->interest_label,
            "Fit Score atual: " . ($lead->fit_score !== null ? $lead->fit_score . '/100' : '(não avaliado)'),
            "Orçamento estimado: " . ($lead->estimated_budget ?: '(não informado)'),
            "Valor potencial: " . ($lead->potential_value ? 'R$ ' . number_format($lead->potential_value, 2, ',', '.') : '(não informado)'),
        ];

        if ($lead->pain_points) {
            $parts[] = "\nDores identificadas:\n{$lead->pain_points}";
        }

        if ($lead->opportunity_summary) {
            $parts[] = "\nResumo da oportunidade:\n{$lead->opportunity_summary}";
        }

        if ($lead->notes) {
            $parts[] = "\nObservações:\n{$lead->notes}";
        }

        if ($activities) {
            $parts[] = "\n=== ATIVIDADES RECENTES ===\n{$activities}";
        }

        if ($notes) {
            $parts[] = "\n=== NOTAS ===\n{$notes}";
        }

        return implode("\n", $parts);
    }
}
