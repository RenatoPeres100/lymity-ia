<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Client;
use App\Services\Commercial\BudgetService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(protected BudgetService $service) {}

    public function index(Request $request)
    {
        $budgets = Budget::with('client')
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.budgets.index', compact('budgets', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.budgets.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'title'                 => 'required|string|max:255',
            'description'           => 'nullable|string',
            'month'                 => 'nullable|integer|min:1|max:12',
            'year'                  => 'nullable|integer|min:2024|max:2100',
            'items'                 => 'nullable|array',
            'items.*.category'      => 'required_with:items|in:media,production,service,tool,other',
            'items.*.name'          => 'required_with:items|string',
            'items.*.description'   => 'nullable|string',
            'items.*.amount'        => 'required_with:items|numeric|min:0',
        ]);

        $budget = $this->service->createBudget($data, auth()->user());

        return redirect()->route('admin.budgets.show', $budget)
            ->with('success', 'Orçamento criado com sucesso!');
    }

    public function show(Budget $budget)
    {
        $budget->load(['client', 'items', 'approvalRequests.actions']);
        return view('admin.budgets.show', compact('budget'));
    }

    public function edit(Budget $budget)
    {
        $clients = Client::orderBy('name')->get();
        $budget->load('items');
        return view('admin.budgets.edit', compact('budget', 'clients'));
    }

    public function update(Request $request, Budget $budget)
    {
        $data = $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'title'                 => 'required|string|max:255',
            'description'           => 'nullable|string',
            'month'                 => 'nullable|integer|min:1|max:12',
            'year'                  => 'nullable|integer|min:2024|max:2100',
            'items'                 => 'nullable|array',
            'items.*.category'      => 'required_with:items|in:media,production,service,tool,other',
            'items.*.name'          => 'required_with:items|string',
            'items.*.description'   => 'nullable|string',
            'items.*.amount'        => 'required_with:items|numeric|min:0',
        ]);

        $this->service->updateBudget($budget, $data, auth()->user());

        return redirect()->route('admin.budgets.show', $budget)
            ->with('success', 'Orçamento atualizado!');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('admin.budgets.index')
            ->with('success', 'Orçamento removido.');
    }

    public function sendApproval(Budget $budget)
    {
        $this->service->sendToApproval($budget, auth()->user());
        return back()->with('success', 'Orçamento enviado para aprovação!');
    }
}
