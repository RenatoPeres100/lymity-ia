<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiWorkScheduleRequest;
use App\Http\Requests\UpdateAiWorkScheduleRequest;
use App\Models\AiEmployee;
use App\Models\AiWorkSchedule;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiWorkScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = AiWorkSchedule::with(['employee', 'client'])->latest()->paginate(20);
        return view('admin.ai-schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        $employees = AiEmployee::where('status', 'active')->orderBy('name')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.ai-schedules.create', compact('employees', 'clients'));
    }

    public function store(StoreAiWorkScheduleRequest $request): RedirectResponse
    {
        AiWorkSchedule::create($request->validated());
        return redirect()->route('admin.ai-schedules.index')
            ->with('success', 'Agendamento criado com sucesso.');
    }

    public function edit(AiWorkSchedule $aiSchedule): View
    {
        $employees = AiEmployee::orderBy('name')->get();
        $clients   = Client::orderBy('name')->get();
        return view('admin.ai-schedules.edit', compact('aiSchedule', 'employees', 'clients'));
    }

    public function update(UpdateAiWorkScheduleRequest $request, AiWorkSchedule $aiSchedule): RedirectResponse
    {
        $aiSchedule->update($request->validated());
        return redirect()->route('admin.ai-schedules.index')
            ->with('success', 'Agendamento atualizado.');
    }

    public function destroy(AiWorkSchedule $aiSchedule): RedirectResponse
    {
        $aiSchedule->delete();
        return redirect()->route('admin.ai-schedules.index')
            ->with('success', 'Agendamento removido.');
    }

    public function toggle(AiWorkSchedule $aiSchedule): RedirectResponse
    {
        $aiSchedule->update(['active' => !$aiSchedule->active]);
        $state = $aiSchedule->active ? 'ativado' : 'pausado';
        return back()->with('success', "Agendamento {$state}.");
    }
}
