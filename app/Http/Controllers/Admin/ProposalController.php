<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Proposal;
use App\Services\Commercial\ProposalService;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function __construct(protected ProposalService $service) {}

    public function index(Request $request)
    {
        $proposals = Proposal::with(['client', 'creator'])
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.proposals.index', compact('proposals', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.proposals.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'terms'       => 'nullable|string',
            'items'                   => 'nullable|array',
            'items.*.name'            => 'required_with:items|string',
            'items.*.description'     => 'nullable|string',
            'items.*.quantity'        => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price'      => 'required_with:items|numeric|min:0',
        ]);

        $proposal = $this->service->createProposal($data, auth()->user());

        return redirect()->route('admin.proposals.show', $proposal)
            ->with('success', 'Proposta criada com sucesso!');
    }

    public function show(Proposal $proposal)
    {
        $proposal->load(['client', 'creator', 'approver', 'items', 'approvalRequests.actions']);
        return view('admin.proposals.show', compact('proposal'));
    }

    public function edit(Proposal $proposal)
    {
        $clients = Client::orderBy('name')->get();
        $proposal->load('items');
        return view('admin.proposals.edit', compact('proposal', 'clients'));
    }

    public function update(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'client_id'               => 'required|exists:clients,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'valid_until'             => 'nullable|date',
            'terms'                   => 'nullable|string',
            'items'                   => 'nullable|array',
            'items.*.name'            => 'required_with:items|string',
            'items.*.description'     => 'nullable|string',
            'items.*.quantity'        => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price'      => 'required_with:items|numeric|min:0',
        ]);

        $this->service->updateProposal($proposal, $data, auth()->user());

        return redirect()->route('admin.proposals.show', $proposal)
            ->with('success', 'Proposta atualizada!');
    }

    public function destroy(Proposal $proposal)
    {
        $proposal->delete();
        return redirect()->route('admin.proposals.index')
            ->with('success', 'Proposta removida.');
    }

    public function sendApproval(Proposal $proposal)
    {
        $this->service->sendToInternalApproval($proposal, auth()->user());
        return back()->with('success', 'Proposta enviada para aprovação interna!');
    }

    public function sendClient(Proposal $proposal)
    {
        try {
            $this->service->sendToClient($proposal, auth()->user());
            return back()->with('success', 'Proposta enviada ao cliente!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function generateAi(Request $request)
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $result = $this->service->generateWithAi($data, auth()->user());

        if ($result instanceof Proposal) {
            return redirect()->route('admin.proposals.show', $result)
                ->with('success', 'Proposta gerada por IA com sucesso!');
        }

        return back()->with('success', 'Tarefa IA criada!');
    }
}
