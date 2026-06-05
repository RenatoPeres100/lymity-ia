<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Services\Clients\ClientManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Traits\HasBulkDestroy;

class ClientController extends Controller
{
    use HasBulkDestroy;

    protected string $bulkDestroyModel  = Client::class;

    public function __construct(private ClientManagementService $svc) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $user  = Auth::user();
        $query = Client::visibleTo($user)
            ->with(['company'])
            ->withCount('users')
            ->search($request->input('search'))
            ->orderBy('name');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['active', 'inactive']);
        }

        $clients  = $query->paginate(20)->withQueryString();
        $stats    = [
            'total'    => Client::visibleTo($user)->count(),
            'active'   => Client::visibleTo($user)->active()->count(),
            'inactive' => Client::visibleTo($user)->inactive()->count(),
            'archived' => Client::visibleTo($user)->archived()->count(),
        ];

        return view('admin.clients.index', compact('clients', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->svc->createClient($request->validated(), Auth::user());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Cliente {$client->name} criado com sucesso.");
    }

    public function show(Client $client): View
    {
        $this->authorize('view', $client);

        $client->load(['company', 'users', 'brandProfile']);

        $pendingApprovals = 0;
        try {
            $pendingApprovals = $client->approvalRequests()->where('status', 'pending')->count();
        } catch (\Throwable) {}

        $recentLogs = ActivityLog::where(function ($q) use ($client) {
            $q->where('client_id', $client->id)
              ->orWhere(function ($q2) use ($client) {
                  $q2->where('subject_type', Client::class)->where('subject_id', $client->id);
              });
        })->orderByDesc('created_at')->limit(10)->get();

        return view('admin.clients.show', compact('client', 'pendingApprovals', 'recentLogs'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->svc->updateClient($client, $request->validated(), Auth::user());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function disable(Client $client): RedirectResponse
    {
        $this->authorize('disable', $client);
        $this->svc->disable($client, Auth::user());

        return back()->with('success', "Cliente {$client->name} desativado.");
    }

    public function activate(Client $client): RedirectResponse
    {
        $this->authorize('activate', $client);
        $this->svc->activate($client, Auth::user());

        return back()->with('success', "Cliente {$client->name} ativado.");
    }

    public function archive(Client $client): RedirectResponse
    {
        $this->authorize('archive', $client);
        $this->svc->archive($client, Auth::user());

        return redirect()->route('admin.clients.index')
            ->with('success', "Cliente {$client->name} arquivado.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        // Destroy = archive, never physical delete
        $this->authorize('delete', $client);
        $this->svc->archive($client, Auth::user());

        return redirect()->route('admin.clients.index')
            ->with('success', "Cliente {$client->name} arquivado.");
    }
}
