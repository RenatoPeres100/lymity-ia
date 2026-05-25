<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencySetting;
use App\Services\Ai\AiCostService;
use App\Services\Ai\AiGenerationService;
use App\Services\Ai\AiProviderManager;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    public function __construct(
        private AiProviderManager  $manager,
        private AiCostService      $costService,
        private AiGenerationService $generationService,
    ) {}

    public function index()
    {
        $provider    = config('ai.provider', 'mock');
        $model       = config('ai.model', 'mock-growth-agent');
        $realEnabled = config('ai.real_enabled', false);
        $hasApiKey   = !empty(config('ai.api_key'));
        $usage       = $this->costService->getUsageSummary();

        // Saved limits (may override .env in UI)
        $dailyLimit   = AgencySetting::get('ai_daily_task_limit',   config('ai.daily_task_limit', 50));
        $monthlyLimit = AgencySetting::get('ai_monthly_budget_limit', config('ai.monthly_budget_limit', 100));

        return view('admin.ai-settings.index', compact(
            'provider', 'model', 'realEnabled', 'hasApiKey', 'usage',
            'dailyLimit', 'monthlyLimit',
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'daily_task_limit'     => 'required|integer|min:1|max:1000',
            'monthly_budget_limit' => 'required|numeric|min:0|max:10000',
        ]);

        AgencySetting::set('ai_daily_task_limit',     $validated['daily_task_limit'],     'integer');
        AgencySetting::set('ai_monthly_budget_limit', $validated['monthly_budget_limit'], 'float');

        return redirect()->route('admin.ai-settings.index')
            ->with('success', 'Limites de IA atualizados com sucesso.');
    }

    public function testForm()
    {
        $provider = config('ai.provider', 'mock');
        $model    = config('ai.model', 'mock-growth-agent');
        $hasApiKey = !empty(config('ai.api_key'));

        return view('admin.ai-settings.test', compact('provider', 'model', 'hasApiKey'));
    }

    public function testConnection(Request $request)
    {
        $provider = $this->manager->provider();
        $result   = $provider->testConnection();

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        // Test with a short prompt if requested
        $generationResult = null;
        if ($request->has('run_generation')) {
            $request->validate(['test_prompt' => 'nullable|string|max:500']);

            $testPrompt = $request->input('test_prompt', 'Escreva 1 frase motivacional sobre marketing digital.');
            $generationResult = $this->generationService->generateDirect([
                'task_type'   => 'generate_social_post',
                'title'       => 'Teste de Conexão — Lymity IA',
                'description' => $testPrompt,
                'client_name' => 'Lymity IA Demo',
            ]);
        }

        $model    = config('ai.model', 'mock-growth-agent');
        $hasApiKey = !empty(config('ai.api_key'));

        return view('admin.ai-settings.test', compact(
            'result', 'generationResult', 'model', 'hasApiKey',
        ))->with('provider', config('ai.provider', 'mock'));
    }
}
