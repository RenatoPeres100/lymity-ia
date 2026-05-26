<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunAgentRoutineJob;
use App\Models\ActivityLog;
use App\Models\AgentRoutine;
use App\Models\AiEmployee;
use App\Models\Company;
use App\Services\Agents\AgentRoutineService;
use Illuminate\Http\Request;

class AgentRoutineController extends Controller
{
    public function __construct(private AgentRoutineService $service) {}

    public function index()
    {
        $routines = AgentRoutine::with('aiEmployee', 'company')
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

        $company = Company::first();
        $validated['company_id']        = $company?->id;
        $validated['client_id']         = null;
        $validated['active']            = $request->boolean('active', true);
        $validated['requires_approval'] = $request->boolean('requires_approval', true);

        $routine = AgentRoutine::create($validated);
        $routine->update(['next_run_at' => $this->service->calculateNextRun($routine)]);

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

        $agentRoutine->update($validated);
        $agentRoutine->update(['next_run_at' => $this->service->calculateNextRun($agentRoutine)]);

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
        try {
            RunAgentRoutineJob::dispatch($agentRoutine->id);
            $this->log('agent_routine_run_now', $request->user(), $agentRoutine);

            return redirect()->back()->with('success', "Rotina \"{$agentRoutine->title}\" enfileirada para execução imediata.");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['run' => 'Erro ao enfileirar: ' . $e->getMessage()]);
        }
    }

    public function runs(AgentRoutine $agentRoutine)
    {
        $runs = $agentRoutine->runs()->with('aiEmployee')->latest()->paginate(30);
        return view('admin.agents.routines.runs', ['routine' => $agentRoutine, 'runs' => $runs]);
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
