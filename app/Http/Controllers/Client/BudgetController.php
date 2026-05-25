<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Services\Commercial\BudgetService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(protected BudgetService $service) {}

    public function index()
    {
        $clientId = auth()->user()->client_id;
        $budgets  = Budget::where('client_id', $clientId)->latest()->paginate(20);

        return view('client.budgets.index', compact('budgets'));
    }

    public function show(Budget $budget)
    {
        abort_unless($budget->client_id === auth()->user()->client_id, 403);
        $budget->load('items');
        return view('client.budgets.show', compact('budget'));
    }

    public function approve(Request $request, Budget $budget)
    {
        abort_unless($budget->client_id === auth()->user()->client_id, 403);
        $this->service->approveBudget($budget, auth()->user());
        return back()->with('success', 'Orçamento aprovado!');
    }

    public function reject(Request $request, Budget $budget)
    {
        abort_unless($budget->client_id === auth()->user()->client_id, 403);
        $this->service->rejectBudget($budget, auth()->user());
        return back()->with('success', 'Orçamento rejeitado.');
    }

    public function comment(Request $request, Budget $budget)
    {
        abort_unless($budget->client_id === auth()->user()->client_id, 403);

        $request->validate(['comment' => 'required|string|max:2000']);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'client_id'   => $budget->client_id,
            'action'      => 'budget_comment',
            'module'      => 'commercial',
            'description' => "Comentário no orçamento [{$budget->title}]: " . $request->comment,
        ]);

        return back()->with('success', 'Comentário registrado.');
    }
}
