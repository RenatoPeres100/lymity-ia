<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use App\Services\Users\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserManagementService $svc) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = Auth::user();
        $query = User::with(['company', 'client'])->orderBy('name');

        // client_admin only sees own client's users
        if ($actor->role === 'cliente_admin') {
            $query->where('client_id', $actor->client_id)->where('user_type', 'client');
        }

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
            $query->where('client_id', $clientId);
        }

        $users   = $query->paginate(20)->withQueryString();
        $clients = Client::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total'    => User::count(),
            'active'   => User::where('status', 'active')->count(),
            'inactive' => User::whereIn('status', ['inactive', 'blocked'])->count(),
            'internal' => User::where('user_type', 'internal')->orWhere(fn($q) => $q->where('user_type', 'agency'))->count(),
            'clients'  => User::where('user_type', 'client')->count(),
            'recent'   => User::whereNotNull('last_login_at')->orderByDesc('last_login_at')->limit(5)->get(['id', 'name', 'last_login_at']),
        ];

        return view('admin.users.index', compact('users', 'clients', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $companies = Company::orderBy('name')->get(['id', 'name']);
        $clients   = Client::orderBy('name')->get(['id', 'name']);
        $roles     = $this->availableRoles(Auth::user());

        return view('admin.users.create', compact('companies', 'clients', 'roles'));
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

        $companies = Company::orderBy('name')->get(['id', 'name']);
        $clients   = Client::orderBy('name')->get(['id', 'name']);
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

    private function availableRoles(User $actor): array
    {
        $all = [
            'admin_geral'         => 'Admin Geral',
            'agencia_admin'       => 'Admin Agência',
            'agencia_operador'    => 'Operador',
            'social_media'        => 'Social Media',
            'copywriter'          => 'Copywriter',
            'blog_writer'         => 'Blog Writer',
            'seo'                 => 'SEO',
            'designer'            => 'Designer',
            'gestor_trafego'      => 'Gestor de Tráfego',
            'cliente_admin'       => 'Admin Cliente',
            'cliente_colaborador' => 'Colaborador Cliente',
            'viewer'              => 'Visualizador',
        ];

        if ($actor->isAdminGeral()) {
            return $all;
        }

        unset($all['admin_geral']);
        return $all;
    }
}
