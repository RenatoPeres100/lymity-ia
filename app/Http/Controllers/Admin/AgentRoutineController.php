<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AgentRoutine;
use App\Models\AiEmployee;
use App\Models\Company;
use App\Services\Agents\AgentRoutineExecutionService;
use App\Services\Agents\AgentRoutineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentRoutineController extends Controller
{
    public function __construct(
        private AgentRoutineService          $service,
        private AgentRoutineExecutionService $engine,
    ) {}

    public function index()
    {
        $user     = Auth::user();
        $routines = AgentRoutine::visibleTo($user)
            ->with('aiEmployee', 'company')
            ->latest()
            ->paginate(20);

        return view('admin.agents.routines.index', compact('routines'));
    }

    public function create()
    {
        $employees = AiEmployee::where('status', 'active')->get();
        $company   = Company::first();

        return view('admin.agents.routines.create', compact('employees', 'company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ai_employee_id'    => 'required|exists:ai_employees,id',
            'routine_type'      => 'required|string',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'frequency'         => 'required|in:daily,weekly,monthly,manual',
            'days_of_week'      => 'nullable|array',
            'days_of_week.*'    => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_of_day'       => 'nullable|date_format:H:i',
            'content_quantity'  => 'nullable|integer|min:1|max:20',
            'quantity_per_run'  => 'nullable|integer|min:1|max:20',
            'approval_lead_days'=> 'nullable|integer|min:0|max:14',
            'publication_time'  => 'nullable|date_format:H:i',
            'active'            => 'boolean',
            'requires_approval' => 'boolean',
        ]);

        $company = Company::first();
        $validated['company_id']        = $company?->id;
        $validated['client_id']         = null;
        $validated['active']            = $request->boolean('active', true);
        $validated['status']            = $request->boolean('active', true) ? 'active' : 'paused';
        $validated['requires_approval'] = $request->boolean('requires_approval', true);
        // Sync quantity_per_run and content_quantity
        $qty = (int)($validated['quantity_per_run'] ?? $validated['content_quantity'] ?? 1);
        $validated['quantity_per_run']  = $qty;
        $validated['content_quantity']  = $qty;

        $routine = AgentRoutine::create($validated);
        $routine->update(['next_run_at' => $this->engine->calculateNextRunAt($routine)]);

        $this->log('agent_routine_created', $request->user(), $routine);

        return redirect()->route('admin.agents.routines.index')
            ->with('success', "Rotina \"{$routine->title}\" criada com sucesso.");
    }

    public function show(AgentRoutine $agentRoutine)
    {
        $agentRoutine->load('aiEmployee', 'company', 'runs');
        return view('admin.agents.routines.show', ['routine' => $agentRoutine]);
    }

    public function edit(AgentRoutine $agentRoutine)
    {
        $employees = AiEmployee::where('status', 'active')->get();
        return view('admin.agents.routines.edit', ['routine' => $agentRoutine, 'employees' => $employees]);
    }

    public function update(Request $request, AgentRoutine $agentRoutine)
    {
        $validated = $request->validate([
            'ai_employee_id'   => 'required|exists:ai_employees,id',
            'routine_type'     => 'required|in:social_post_creation,blog_post_creation,copy_improvement,content_review',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'frequency'        => 'required|in:daily,weekly,monthly',
            'days_of_week'     => 'nullable|array',
            'days_of_week.*'   => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_of_day'      => 'nullable|date_format:H:i',
            'content_quantity' => 'required|integer|min:1|max:10',
            'active'           => 'boolean',
            'requires_approval'=> 'boolean',
        ]);

        $validated['active']            = $request->boolean('active', true);
        $validated['requires_approval'] = $request->boolean('requires_approval', true);

        $qty = (int)($validated['quantity_per_run'] ?? $validated['content_quantity'] ?? 1);
        $validated['quantity_per_run'] = $qty;
        $validated['content_quantity'] = $qty;
        $validated['status'] = $validated['active'] ? 'active' : 'paused';

        $agentRoutine->update($validated);
        $agentRoutine->update(['next_run_at' => $this->engine->calculateNextRunAt($agentRoutine)]);

        $this->log('agent_routine_updated', $request->user(), $agentRoutine);

        return redirect()->route('admin.agents.routines.index')
            ->with('success', "Rotina \"{$agentRoutine->title}\" atualizada.");
    }

    public function pause(Request $request, AgentRoutine $agentRoutine)
    {
        $agentRoutine->update(['active' => false]);
        $this->log('agent_routine_paused', $request->user(), $agentRoutine);

        return redirect()->back()->with('success', "Rotina \"{$agentRoutine->title}\" pausada.");
    }

    public function activate(Request $request, AgentRoutine $agentRoutine)
    {
        $agentRoutine->update(['active' => true]);
        $this->log('agent_routine_activated', $request->user(), $agentRoutine);

        return redirect()->back()->with('success', "Rotina \"{$agentRoutine->title}\" ativada.");
    }

    public function runNow(Request $request, AgentRoutine $agentRoutine)
    {
        // Force next_run_at to past so engine picks it up
        $agentRoutine->update(['next_run_at' => now()->subMinute()]);

        try {
            $run = $this->engine->runRoutine($agentRoutine, now(), false);
            $this->log('agent_routine_run_now', $request->user(), $agentRoutine);

            $msg = "Rotina \"{$agentRoutine->title}\" executada. ";
            $msg .= "Items criados: {$run->items_created} | Aprovações: {$run->approvals_created}";

            if ($run->status === 'failed') {
                return redirect()->back()->withErrors(['run' => 'Execução falhou: ' . $run->error_message]);
            }

            return redirect()->route('admin.agents.routines.runs', $agentRoutine)
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['run' => 'Erro ao executar: ' . $e->getMessage()]);
        }
    }

    public function runs(AgentRoutine $agentRoutine)
    {
        $runs = $agentRoutine->runs()->with('aiEmployee')->latest()->paginate(30);
        return view('admin.agents.routines.runs', ['routine' => $agentRoutine, 'runs' => $runs]);
    }

    public function destroy(Request $request, AgentRoutine $agentRoutine)
    {
        $title = $agentRoutine->title;
        $agentRoutine->runs()->delete();
        $agentRoutine->delete();

        $this->log('agent_routine_deleted', $request->user());

        return redirect()->route('admin.agents.routines.index')
            ->with('success', "Rotina \"{$title}\" excluída com sucesso.");
    }

    private function log(string $action, $user, ?AgentRoutine $routine = null): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'agent_routines',
                'level'       => 'info',
                'description' => "Rotina: " . ($routine?->title ?? ''),
                'metadata'    => ['routine_id' => $routine?->id],
            ]);
        } catch (\Throwable) {}
    }
}
