<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiMemoryRequest;
use App\Http\Requests\UpdateAiMemoryRequest;
use App\Models\AiEmployee;
use App\Models\AiMemory;
use App\Models\Client;
use App\Services\Ai\AiMemoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiMemoryController extends Controller
{
    public function __construct(private AiMemoryService $service) {}

    public function index(Request $request): View
    {
        $query = AiMemory::with(['employee', 'client'])->latest();

        if ($request->filled('employee')) {
            $query->where('ai_employee_id', $request->employee);
        }
        if ($request->filled('type')) {
            $query->where('memory_type', $request->type);
        }

        $memories  = $query->paginate(30)->withQueryString();
        $employees = AiEmployee::orderBy('name')->get();

        return view('admin.ai-memories.index', compact('memories', 'employees'));
    }

    public function create(): View
    {
        $employees = AiEmployee::orderBy('name')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.ai-memories.create', compact('employees', 'clients'));
    }

    public function store(StoreAiMemoryRequest $request): RedirectResponse
    {
        $this->service->store(
            $request->ai_employee_id,
            $request->memory_type,
            $request->title,
            $request->content,
            $request->client_id,
            $request->weight ?? 1,
        );

        return redirect()->route('admin.ai-memories.index')
            ->with('success', 'Memória criada com sucesso.');
    }

    public function edit(AiMemory $aiMemory): View
    {
        $employees = AiEmployee::orderBy('name')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.ai-memories.edit', compact('aiMemory', 'employees', 'clients'));
    }

    public function update(UpdateAiMemoryRequest $request, AiMemory $aiMemory): RedirectResponse
    {
        $this->service->update($aiMemory, $request->validated());
        return redirect()->route('admin.ai-memories.index')
            ->with('success', 'Memória atualizada.');
    }

    public function destroy(AiMemory $aiMemory): RedirectResponse
    {
        $this->service->delete($aiMemory);
        return redirect()->route('admin.ai-memories.index')
            ->with('success', 'Memória removida.');
    }
}
