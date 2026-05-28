<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use App\Services\Dashboard\DashboardStatsService;
use App\Services\Users\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private UserManagementService $svc,
        private DashboardStatsService $dashStats,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = Auth::user();
        $query = User::visibleTo($actor)->with(['company', 'client'])->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }
        if ($type = $request->input('user_type')) {
            $query->where('user_type', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($clientId = $request->input('client_id')) {
            // Only allow filtering by clients visible to actor
            if (app(\App\Services\Auth\AccessScopeService::class)->canSeeClient($actor, (int) $clientId)) {
                $query->where('client_id', $clientId);
            }
        }

        $users   = $query->paginate(20)->withQueryString();
        $clients = Client::visibleTo($actor)->orderBy('name')->get(['id', 'name']);

        // Single aggregated query replaces 5 separate count queries
        $stats = $this->dashStats->getUserStats($actor);
        $stats['recent'] = User::visibleTo($actor)
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit(5)
            ->get(['id', 'name', 'last_login_at']);

        return view('admin.users.index', compact('users', 'clients', 'stats'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $actor      = Auth::user();
        $companies  = Company::visibleTo($actor)->orderBy('name')->get(['id', 'name']);
        $clients    = Client::visibleTo($actor)->orderBy('name')->get(['id', 'name']);
        $roles      = $this->availableRoles(Auth::user());
        $prefillClientId  = $request->input('client_id');
        $prefillUserType  = $request->input('user_type');

        return view('admin.users.create', compact('companies', 'clients', 'roles', 'prefillClientId', 'prefillUserType'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->svc->createUser($request->validated(), Auth::user());

        $msg = "Usuário {$user->name} criado com sucesso.";
        if ($user->plain_password) {
            session()->flash('temp_password', $user->plain_password);
            $msg .= ' Uma senha temporária foi gerada.';
        }

        return redirect()->route('admin.users.show', $user)->with('success', $msg);
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['company', 'client', 'permissions']);
        $logs = \App\Models\ActivityLog::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.users.show', compact('user', 'logs'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $actor     = Auth::user();
        $companies = Company::visibleTo($actor)->orderBy('name')->get(['id', 'name']);
        $clients   = Client::visibleTo($actor)->orderBy('name')->get(['id', 'name']);
        $roles     = $this->availableRoles(Auth::user());

        return view('admin.users.edit', compact('user', 'companies', 'clients', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->svc->updateUser($user, $request->validated(), Auth::user());

        return redirect()->route('admin.users.show', $user)->with('success', 'Usuário atualizado com sucesso.');
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('activate', $user);
        $this->svc->activate($user, Auth::user());

        return back()->with('success', "Usuário {$user->name} ativado.");
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);
        $this->svc->deactivate($user, Auth::user());

        return back()->with('success', "Usuário {$user->name} inativado.");
    }

    public function block(User $user): RedirectResponse
    {
        $this->authorize('block', $user);
        $this->svc->block($user, Auth::user());

        return back()->with('success', "Usuário {$user->name} bloqueado.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $actor = Auth::user();
        $this->authorize('delete', $user);

        if ($user->id === $actor->id) {
            return back()->withErrors(['error' => 'Você não pode excluir sua própria conta.']);
        }

        if ($user->role === 'admin_geral' && !$actor->isAdminGeral()) {
            abort(403, 'Apenas um Admin Geral pode excluir outro Admin Geral.');
        }

        $name = $user->name;

        // Remove permissions first
        $user->permissions()->detach();

        $user->delete();

        \App\Models\ActivityLog::create([
            'user_id'     => $actor->id,
            'action'      => 'user_deleted',
            'module'      => 'users',
            'level'       => 'warning',
            'description' => "Usuário excluído: {$name}",
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuário \"{$name}\" excluído com sucesso.");
    }

    private function availableRoles(User $actor): array
    {
        if ($actor->isAdminGeral()) {
            return [
                'admin_geral' => 'Admin Geral',
                'cliente'     => 'Cliente',
                'colaborador' => 'Colaborador',
            ];
        }

        // Cliente can only create collaborators for their own client
        if ($actor->isCliente()) {
            return ['colaborador' => 'Colaborador'];
        }

        return [];
    }
}
