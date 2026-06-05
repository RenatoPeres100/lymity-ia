<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentTask;
use App\Models\AiEmployee;
use App\Models\AiMemory;
use App\Models\Client;
use App\Services\AI\Memory\AIMemoryService;
use App\Services\Ai\AiMemoryService as LegacyMemoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiMemoryController extends Controller
{
    public function __construct(
        private AIMemoryService     $memoryService,
    ) {}

    public function index(Request $request): View
    {
        $user  = auth()->user();
        $query = AiMemory::with(['employee', 'agentTask', 'company'])->latest();

        if ($user->role !== 'admin_geral' && $user->company_id) {
            $query->where('company_id', $user->company_id);
        }

        if ($request->filled('type')) {
            $query->where('memory_type', $request->type);
        }
        if ($request->filled('employee')) {
            $query->where('ai_employee_id', $request->employee);
        }
        if ($request->has('active') && $request->active !== '') {
            $query->where('active', (bool) $request->active);
        }

        $memories  = $query->paginate(30)->withQueryString();
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $types     = [
            'brand_rule'          => 'Regra de Marca',
            'task_rule'           => 'Regra de Tarefa',
            'approved_pattern'    => 'Padrão Aprovado',
            'rejected_pattern'    => 'Padrão Rejeitado',
            'feedback'            => 'Feedback',
            'content_preference'  => 'Preferência de Conteúdo',
            'visual_preference'   => 'Preferência Visual',
            'performance_insight' => 'Insight de Performance',
            'warning'             => 'Aviso',
            'general'             => 'Geral',
        ];

        return view('admin.ai-memory.index', compact('memories', 'employees', 'types'));
    }

    public function create(): View
    {
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $tasks     = AgentTask::active()->orderBy('title')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.ai-memory.create', compact('employees', 'tasks', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_employee_id'  => 'nullable|exists:ai_employees,id',
            'agent_task_id'   => 'nullable|exists:agent_tasks,id',
            'memory_type'     => 'required|string|max:40',
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'weight'          => 'nullable|integer|min:1|max:10',
            'relevance_score' => 'nullable|integer|min:0|max:100',
        ]);

        $user                    = auth()->user();
        $validated['company_id'] = $user->company_id;
        $validated['active']     = true;
        $validated['source_type']= 'manual';
        $validated['source_id']  = $user->id;

        $this->memoryService->storeMemory($validated);

        return redirect()->route('admin.ai-memory.index')
            ->with('success', 'Memória criada com sucesso.');
    }

    public function edit(AiMemory $aiMemory): View
    {
        $this->authorizeMemory($aiMemory);
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $tasks     = AgentTask::active()->orderBy('title')->get();
        return view('admin.ai-memory.edit', compact('aiMemory', 'employees', 'tasks'));
    }

    public function update(Request $request, AiMemory $aiMemory): RedirectResponse
    {
        $this->authorizeMemory($aiMemory);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'memory_type'     => 'required|string|max:40',
            'weight'          => 'nullable|integer|min:1|max:10',
            'relevance_score' => 'nullable|integer|min:0|max:100',
        ]);

        $validated['compact_content'] = null;
        $aiMemory->update($validated);

        return redirect()->route('admin.ai-memory.index')
            ->with('success', 'Memória atualizada.');
    }

    public function deactivate(AiMemory $aiMemory): RedirectResponse
    {
        $this->authorizeMemory($aiMemory);
        $this->memoryService->deactivateMemory($aiMemory, auth()->user());
        return back()->with('success', 'Memória desativada.');
    }

    public function activate(AiMemory $aiMemory): RedirectResponse
    {
        $this->authorizeMemory($aiMemory);
        $aiMemory->update(['active' => true]);
        return back()->with('success', 'Memória ativada.');
    }

    public function destroy(AiMemory $aiMemory): RedirectResponse
    {
        $this->authorizeMemory($aiMemory);
        $aiMemory->delete();
        return redirect()->route('admin.ai-memory.index')
            ->with('success', 'Memória removida.');
    }

    private function authorizeMemory(AiMemory $memory): void
    {
        $user = auth()->user();
        if ($user->role === 'admin_geral') return;
        if ($memory->company_id && $user->company_id !== $memory->company_id) {
            abort(403, 'Acesso negado.');
        }
    }
}
