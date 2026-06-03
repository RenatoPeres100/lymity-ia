<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prospecting\GenerateProspectMessageRequest;
use App\Models\ProspectLead;
use App\Services\Prospecting\ProspectingAiService;
use Illuminate\Http\Request;
use RuntimeException;

class AiAssistantController extends Controller
{
    public function __construct(private ProspectingAiService $aiService) {}

    public function analyze(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('generateAi', [$lead, 'analyze']), 403);

        try {
            $insight = $this->aiService->analyzeLead($lead, $request->user());
            return redirect()->route('admin.prospecting.leads.show', $lead)
                ->with('success', "Análise IA gerada: \"{$insight->title}\"");
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function qualify(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('generateAi', [$lead, 'qualify']), 403);

        try {
            $insight = $this->aiService->qualifyLead($lead, $request->user());
            return redirect()->route('admin.prospecting.leads.show', $lead)
                ->with('success', "Qualificação IA gerada. Fit score sugerido: {$insight->score}/100");
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function generateMessage(GenerateProspectMessageRequest $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('view', $lead), 403);

        try {
            $suggestion = $this->aiService->generateMessage(
                $lead,
                $request->channel,
                $request->objective,
                $request->user()
            );
            return redirect()->route('admin.prospecting.leads.show', $lead)
                ->with('success', "Mensagem IA gerada para canal \"{$suggestion->channel_label}\".");
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function suggestNextAction(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('generateAi', [$lead, 'suggest_next_action']), 403);

        try {
            $insight = $this->aiService->suggestNextAction($lead, $request->user());
            return redirect()->route('admin.prospecting.leads.show', $lead)
                ->with('success', "Próxima ação sugerida pela IA.");
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function summarize(Request $request, ProspectLead $lead)
    {
        abort_unless($request->user()->can('generateAi', [$lead, 'summarize']), 403);

        try {
            $insight = $this->aiService->summarizeHistory($lead, $request->user());
            return redirect()->route('admin.prospecting.leads.show', $lead)
                ->with('success', "Resumo do histórico gerado pela IA.");
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
