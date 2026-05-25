<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContract;
use App\Services\Commercial\ContractService;
use Illuminate\Http\Request;

class ClientContractController extends Controller
{
    public function __construct(protected ContractService $service) {}

    public function index(Request $request)
    {
        $contracts = ClientContract::with('client')
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();

        return view('admin.contracts.index', compact('contracts', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.contracts.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path'   => 'nullable|string|max:500',
            'status'      => 'required|in:draft,pending_signature,signed,canceled,archived',
        ]);

        $contract = $this->service->createContract($data, auth()->user());

        return redirect()->route('admin.contracts.show', $contract)
            ->with('success', 'Contrato criado com sucesso!');
    }

    public function show(ClientContract $clientContract)
    {
        $clientContract->load('client');
        return view('admin.contracts.show', compact('clientContract'));
    }

    public function edit(ClientContract $clientContract)
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.contracts.edit', compact('clientContract', 'clients'));
    }

    public function update(Request $request, ClientContract $clientContract)
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path'   => 'nullable|string|max:500',
            'status'      => 'required|in:draft,pending_signature,signed,canceled,archived',
        ]);

        $clientContract->update($data);

        return redirect()->route('admin.contracts.show', $clientContract)
            ->with('success', 'Contrato atualizado!');
    }

    public function pendingSignature(ClientContract $clientContract)
    {
        $this->service->markPendingSignature($clientContract, auth()->user());
        return back()->with('success', 'Contrato marcado como aguardando assinatura.');
    }

    public function markSigned(ClientContract $clientContract)
    {
        $this->service->markSigned($clientContract, auth()->user());
        return back()->with('success', 'Contrato marcado como assinado!');
    }

    public function cancel(ClientContract $clientContract)
    {
        $this->service->cancel($clientContract, auth()->user());
        return back()->with('success', 'Contrato cancelado.');
    }
}
