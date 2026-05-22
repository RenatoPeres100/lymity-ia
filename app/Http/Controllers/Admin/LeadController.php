<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        $leads = Lead::orderByDesc('created_at')->paginate(25);

        return view('admin.leads.index', compact('leads'));
    }

    public function show(Lead $lead): View
    {
        return view('admin.leads.show', compact('lead'));
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,qualified,lost,converted'],
            'notes'  => ['nullable', 'string'],
        ]);

        $lead->update($data);

        return redirect()->route('admin.leads.index')
            ->with('success', 'Status do lead atualizado!');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead excluído com sucesso!');
    }
}
