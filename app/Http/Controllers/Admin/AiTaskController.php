<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiTaskRequest;
use App\Http\Requests\StoreAiFeedbackRequest;
use App\Jobs\RunAiTaskJob;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\AiFeedback;
use App\Models\Client;
use App\Services\Ai\AiTaskService;
use App\Traits\HasBulkDestroy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiTaskController extends Controller
{
    use HasBulkDestroy;

    protected string $bulkDestroyModel  = AiTask::class;
    protected string $bulkDestroyScope  = 'company_id';

    public function __construct(private AiTaskService $service) {}

    public function index(Request $request): View
    {
        $user  = Auth::user();
        $query = AiTask::visibleTo($user)->with(['employee', 'client'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee')) {
            $query->where('ai_employee_id', $request->employee);
        }

        $tasks     = $query->paginate(20)->withQueryString();
        $employees = AiEmployee::orderBy('name')->get();

        return view('admin.ai-tasks.index', compact('tasks', 'employees'));
    }

    public function create(): View
    {
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $clients   = Client::visibleTo(Auth::user())->orderBy('name')->get();
        return view('admin.ai-tasks.create', compact('employees', 'clients'));
    }

    public function store(StoreAiTaskRequest $request): RedirectResponse
    {
        $employee = AiEmployee::findOrFail($request->ai_employee_id);
        $task     = $this->service->createManualTask($employee, $request->validated());

        return redirect()->route('admin.ai-tasks.show', $task)
            ->with('success', 'Tarefa criada com sucesso.');
    }

    public function show(AiTask $aiTask): View
    {
        $aiTask->load(['employee', 'client', 'logs', 'feedbacks.user']);
        return view('admin.ai-tasks.show', compact('aiTask'));
    }

    public function run(AiTask $aiTask): RedirectResponse
    {
        if (!$aiTask->canRun()) {
            return back()->with('error', 'Tarefa não pode ser executada no status atual.');
        }

        RunAiTaskJob::dispatch($aiTask->id);

        return back()->with('success', 'Tarefa enviada para execução.');
    }

    public function approve(AiTask $aiTask): RedirectResponse
    {
        try {
            $this->service->approveTask($aiTask);
            return back()->with('success', 'Tarefa aprovada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, AiTask $aiTask): RedirectResponse
    {
        try {
            $this->service->rejectTask($aiTask, $request->input('reason', ''));
            return back()->with('success', 'Tarefa rejeitada.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(AiTask $aiTask): RedirectResponse
    {
        try {
            $this->service->cancelTask($aiTask);
            return back()->with('success', 'Tarefa cancelada.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeFeedback(StoreAiFeedbackRequest $request, AiTask $aiTask): RedirectResponse
    {
        AiFeedback::updateOrCreate(
            ['ai_task_id' => $aiTask->id, 'user_id' => auth()->id()],
            array_merge($request->validated(), [
                'ai_employee_id' => $aiTask->ai_employee_id,
                'user_id'        => auth()->id(),
            ])
        );

        return back()->with('success', 'Feedback registrado.');
    }

    public function destroy(AiTask $aiTask): RedirectResponse
    {
        $aiTask->delete();
        return redirect()->route('admin.ai-tasks.index')->with('success', 'Tarefa excluída.');
    }
}
