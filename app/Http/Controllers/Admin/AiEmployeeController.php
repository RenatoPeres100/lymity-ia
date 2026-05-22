<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiEmployeeRequest;
use App\Http\Requests\UpdateAiEmployeeRequest;
use App\Models\AiEmployee;
use App\Models\AiSkill;
use App\Models\Client;
use App\Services\Ai\AiEmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiEmployeeController extends Controller
{
    public function __construct(private AiEmployeeService $service) {}

    public function index(): View
    {
        $employees = AiEmployee::with('skills')->orderBy('name')->get();
        return view('admin.ai-employees.index', compact('employees'));
    }

    public function create(): View
    {
        $skills  = AiSkill::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('admin.ai-employees.create', compact('skills', 'clients'));
    }

    public function store(StoreAiEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->service->create($request->validated());
        return redirect()->route('admin.ai-employees.show', $employee)
            ->with('success', "Funcionário IA \"{$employee->name}\" criado com sucesso.");
    }

    public function show(AiEmployee $aiEmployee): View
    {
        $aiEmployee->load(['skills', 'creator', 'tasks' => fn($q) => $q->latest()->take(10), 'schedules', 'memories']);
        return view('admin.ai-employees.show', compact('aiEmployee'));
    }

    public function edit(AiEmployee $aiEmployee): View
    {
        $aiEmployee->load('skills');
        $skills  = AiSkill::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('admin.ai-employees.edit', compact('aiEmployee', 'skills', 'clients'));
    }

    public function update(UpdateAiEmployeeRequest $request, AiEmployee $aiEmployee): RedirectResponse
    {
        $this->service->update($aiEmployee, $request->validated());
        return redirect()->route('admin.ai-employees.show', $aiEmployee)
            ->with('success', 'Funcionário IA atualizado com sucesso.');
    }

    public function destroy(AiEmployee $aiEmployee): RedirectResponse
    {
        $aiEmployee->delete();
        return redirect()->route('admin.ai-employees.index')
            ->with('success', 'Funcionário IA removido.');
    }

    public function pause(AiEmployee $aiEmployee): RedirectResponse
    {
        $this->service->pause($aiEmployee);
        return back()->with('success', "\"{$aiEmployee->name}\" pausado.");
    }

    public function activate(AiEmployee $aiEmployee): RedirectResponse
    {
        $this->service->activate($aiEmployee);
        return back()->with('success', "\"{$aiEmployee->name}\" ativado.");
    }

    public function disable(AiEmployee $aiEmployee): RedirectResponse
    {
        $this->service->disable($aiEmployee);
        return back()->with('success', "\"{$aiEmployee->name}\" desativado.");
    }
}
