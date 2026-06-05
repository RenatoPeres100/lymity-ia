<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunAgentTaskJob;
use App\Models\AgentTask;
use App\Models\AiEmployee;
use App\Models\Client;
use App\Models\Company;
use App\Services\AI\Context\AgentTaskContextService;
use App\Services\AI\Execution\AgentTaskExecutionService;
use App\Services\Logs\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Traits\HasBulkDestroy;

class AgentTaskController extends Controller
{
    use HasBulkDestroy;

    protected string $bulkDestroyModel  = AgentTask::class;
    protected string $bulkDestroyScope  = 'company_id';

    public function __construct(
        private AgentTaskContextService  $contextService,
        private ActivityLogService       $activityLog,
    ) {}

    public function index(Request $request): View
    {
        $query = AgentTask::visibleTo(auth()->user())
            ->with('aiEmployee', 'company', 'client')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }
        if ($request->filled('employee')) {
            $query->where('ai_employee_id', $request->employee);
        }

        $tasks     = $query->paginate(20)->withQueryString();
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();

        return view('admin.agent-tasks.index', compact('tasks', 'employees'));
    }

    public function create(): View
    {
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.agent-tasks.create', compact('employees', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTask($request);

        $user = auth()->user();
        $validated['created_by_user_id'] = $user->id;
        if (!$user->role === 'admin_geral') {
            $validated['company_id'] = $user->company_id;
        }

        $task = AgentTask::create($validated);

        // Generate compact context immediately
        $this->contextService->refreshCompactTaskContext($task);

        // Calculate first run
        app(AgentTaskExecutionService::class)->calculateAndSaveNextRun($task);

        $this->activityLog->info('agent_task.created', "Tarefa criada: {$task->title}", [
            'task_id' => $task->id, 'module' => 'ai_tasks',
        ]);

        return redirect()->route('admin.agent-tasks.show', $task)
            ->with('success', 'Tarefa operacional criada com sucesso.');
    }

    public function show(AgentTask $agentTask): View
    {
        $this->authorizeTask($agentTask);

        $agentTask->load('aiEmployee', 'company', 'client', 'createdBy');
        $runs     = $agentTask->runs()->with('approvalRequest')->latest()->limit(20)->get();
        $packages = $agentTask->generatedPackages()->with('approvalRequest', 'assets')->latest()->limit(10)->get();
        $memories = $agentTask->memories()->active()->orderByDesc('relevance_score')->limit(10)->get();

        return view('admin.agent-tasks.show', compact('agentTask', 'runs', 'packages', 'memories'));
    }

    public function edit(AgentTask $agentTask): View
    {
        $this->authorizeTask($agentTask);
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.agent-tasks.edit', compact('agentTask', 'employees', 'clients'));
    }

    public function update(Request $request, AgentTask $agentTask): RedirectResponse
    {
        $this->authorizeTask($agentTask);

        $validated = $this->validateTask($request);
        $agentTask->update(array_merge($validated, ['version' => $agentTask->version + 1]));

        // Rebuild compact context
        $this->contextService->refreshCompactTaskContext($agentTask);

        // Recalculate next run
        app(AgentTaskExecutionService::class)->calculateAndSaveNextRun($agentTask);

        $this->activityLog->info('agent_task.updated', "Tarefa atualizada: {$agentTask->title}", [
            'task_id' => $agentTask->id, 'module' => 'ai_tasks',
        ]);

        return redirect()->route('admin.agent-tasks.show', $agentTask)
            ->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function runNow(AgentTask $agentTask): RedirectResponse
    {
        $this->authorizeTask($agentTask);

        if (!$agentTask->isActive()) {
            return back()->with('error', 'Tarefa não está ativa.');
        }

        RunAgentTaskJob::dispatch($agentTask);

        $this->activityLog->info('agent_task.run_requested', "Execução manual solicitada: {$agentTask->title}", [
            'task_id' => $agentTask->id, 'module' => 'ai_tasks',
        ]);

        return back()->with('success', 'Tarefa enviada para execução. Verifique as execuções em instantes.');
    }

    public function pause(AgentTask $agentTask): RedirectResponse
    {
        $this->authorizeTask($agentTask);
        $agentTask->update(['status' => 'paused']);

        $this->activityLog->info('agent_task.paused', "Tarefa pausada: {$agentTask->title}", [
            'task_id' => $agentTask->id, 'module' => 'ai_tasks',
        ]);

        return back()->with('success', 'Tarefa pausada.');
    }

    public function activate(AgentTask $agentTask): RedirectResponse
    {
        $this->authorizeTask($agentTask);
        $agentTask->update(['status' => 'active']);

        // Recalculate next run on activation
        app(AgentTaskExecutionService::class)->calculateAndSaveNextRun($agentTask);

        $this->activityLog->info('agent_task.activated', "Tarefa ativada: {$agentTask->title}", [
            'task_id' => $agentTask->id, 'module' => 'ai_tasks',
        ]);

        return back()->with('success', 'Tarefa ativada.');
    }

    public function destroy(AgentTask $agentTask): RedirectResponse
    {
        $this->authorizeTask($agentTask);
        $title = $agentTask->title;
        $agentTask->delete();

        $this->activityLog->info('agent_task.deleted', "Tarefa excluída: {$title}", [
            'module' => 'ai_tasks',
        ]);

        return redirect()->route('admin.agent-tasks.index')
            ->with('success', "Tarefa \"{$title}\" excluída.");
    }

    public function archive(AgentTask $agentTask): RedirectResponse
    {
        $this->authorizeTask($agentTask);
        $agentTask->update(['status' => 'archived']);

        $this->activityLog->info('agent_task.archived', "Tarefa arquivada: {$agentTask->title}", [
            'task_id' => $agentTask->id, 'module' => 'ai_tasks',
        ]);

        return back()->with('success', 'Tarefa arquivada.');
    }

    public function runs(AgentTask $agentTask): View
    {
        $this->authorizeTask($agentTask);
        $runs = $agentTask->runs()
            ->with('aiEmployee', 'approvalRequest', 'contentPackages')
            ->latest()
            ->paginate(20);

        return view('admin.agent-tasks.runs', compact('agentTask', 'runs'));
    }

    public function packages(AgentTask $agentTask): View
    {
        $this->authorizeTask($agentTask);
        $packages = $agentTask->generatedPackages()
            ->with('approvalRequest', 'assets', 'agentTaskRun')
            ->latest()
            ->paginate(20);

        return view('admin.agent-tasks.packages', compact('agentTask', 'packages'));
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function authorizeTask(AgentTask $task): void
    {
        $user = auth()->user();
        if ($user->role === 'admin_geral') return;
        if ($task->company_id && $user->company_id !== $task->company_id) {
            abort(403, 'Acesso negado.');
        }
    }

    private function validateTask(Request $request): array
    {
        return $request->validate([
            'title'                          => 'required|string|max:255',
            'ai_employee_id'                 => 'nullable|exists:ai_employees,id',
            'client_id'                      => 'nullable|exists:clients,id',
            'task_type'                      => 'required|string|max:60',
            'description'                    => 'nullable|string',
            'operational_instructions'       => 'nullable|string',
            'expected_output'                => 'nullable|array',
            'content_channel'                => 'nullable|string|max:50',
            'content_format'                 => 'nullable|string|max:50',
            'requires_external_research'     => 'boolean',
            'external_research_topics'       => 'nullable|string',
            'external_research_recency_days' => 'nullable|integer|min:1|max:365',
            'require_sources'                => 'boolean',
            'if_research_unavailable'        => 'nullable|string|max:30',
            'requires_image'                 => 'boolean',
            'image_type'                     => 'nullable|string|max:30',
            'carousel_slides_count'          => 'nullable|integer|min:2|max:20',
            'image_instructions'             => 'nullable|string',
            'requires_approval'              => 'boolean',
            'auto_schedule_after_approval'   => 'boolean',
            'auto_publish_after_approval'    => 'boolean',
            'frequency'                      => 'required|string|max:20',
            'days_of_week'                   => 'nullable|array',
            'days_of_week.*'                 => 'string',
            'day_of_month'                   => 'nullable|integer|min:1|max:28',
            'time_of_day'                    => 'nullable|date_format:H:i',
            'timezone'                       => 'nullable|string|max:60',
            'content_quantity_per_run'       => 'nullable|integer|min:1|max:10',
            'max_runs_per_day'               => 'nullable|integer|min:1|max:20',
            'status'                         => 'required|string|max:20',
        ]);
    }
}
