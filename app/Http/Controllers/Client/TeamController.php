<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\Users\RolePermissionPreset;
use App\Services\Users\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(private UserManagementService $svc) {}

    public function index(): View
    {
        $user  = Auth::user();

        abort_unless($user->isCliente(), 403, 'Apenas o usuário Cliente pode gerenciar a equipe.');

        $team = User::where('client_id', $user->client_id)
            ->where('role', 'colaborador')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('client.team.index', compact('team'));
    }

    public function create(): View
    {
        $this->authorizeTeamManagement();

        $permissions = RolePermissionPreset::$colaboradorPermissions;
        return view('client.team.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $this->authorizeTeamManagement();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'job_title'   => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
        ]);

        $password = Str::password(12);

        $colaborador = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($password),
            'role'       => 'colaborador',
            'user_type'  => 'client',
            'client_id'  => $actor->client_id,
            'company_id' => optional(optional($actor->client)->company)->id,
            'job_title'  => $data['job_title'] ?? null,
            'status'     => 'active',
        ]);

        // Apply selected permissions
        $permKeys = $data['permissions'] ?? RolePermissionPreset::forRole('colaborador');
        $permIds  = Permission::whereIn('key', $permKeys)->pluck('id')->toArray();
        $colaborador->permissions()->sync($permIds);

        \App\Models\ActivityLog::create([
            'user_id'     => $actor->id,
            'client_id'   => $actor->client_id,
            'action'      => 'colaborador_created',
            'module'      => 'team',
            'level'       => 'info',
            'description' => "Colaborador criado: {$colaborador->name}",
        ]);

        return redirect()->route('client.team.show', $colaborador)
            ->with('success', "Colaborador {$colaborador->name} criado.")
            ->with('temp_password', $password);
    }

    public function show(User $user): View
    {
        $this->authorizeTeamAccess($user);
        $user->load('permissions');
        $availablePermissions = RolePermissionPreset::$colaboradorPermissions;
        return view('client.team.show', compact('user', 'availablePermissions'));
    }

    public function edit(User $user): View
    {
        $this->authorizeTeamAccess($user);
        $availablePermissions = RolePermissionPreset::$colaboradorPermissions;
        $user->load('permissions');
        return view('client.team.edit', compact('user', 'availablePermissions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorizeTeamAccess($user);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'status'    => 'nullable|in:active,inactive',
        ]);

        $user->update($data);

        \App\Models\ActivityLog::create([
            'user_id'   => $actor->id,
            'client_id' => $actor->client_id,
            'action'    => 'colaborador_updated',
            'module'    => 'team',
            'level'     => 'info',
            'description' => "Colaborador atualizado: {$user->name}",
        ]);

        return redirect()->route('client.team.show', $user)
            ->with('success', 'Colaborador atualizado.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $actor = $this->authorizeTeamAccess($user);

        $data = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(RolePermissionPreset::$colaboradorPermissions)),
        ]);

        $permKeys = $data['permissions'] ?? [];
        $permIds  = Permission::whereIn('key', $permKeys)->pluck('id')->toArray();
        $user->permissions()->sync($permIds);

        \App\Models\ActivityLog::create([
            'user_id'   => $actor->id,
            'client_id' => $actor->client_id,
            'action'    => 'colaborador_permissions_updated',
            'module'    => 'team',
            'level'     => 'info',
            'description' => "Permissões do colaborador {$user->name} atualizadas",
        ]);

        return back()->with('success', 'Permissões atualizadas com sucesso.');
    }

    public function activate(User $user): RedirectResponse
    {
        $actor = $this->authorizeTeamAccess($user);
        $user->update(['status' => 'active']);
        return back()->with('success', "{$user->name} ativado.");
    }

    public function deactivate(User $user): RedirectResponse
    {
        $actor = $this->authorizeTeamAccess($user);
        $user->update(['status' => 'inactive']);
        return back()->with('success', "{$user->name} desativado.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $actor = $this->authorizeTeamAccess($user);
        $name  = $user->name;
        $user->permissions()->detach();
        $user->delete();

        \App\Models\ActivityLog::create([
            'user_id'   => $actor->id,
            'client_id' => $actor->client_id,
            'action'    => 'colaborador_deleted',
            'module'    => 'team',
            'level'     => 'warning',
            'description' => "Colaborador excluído: {$name}",
        ]);

        return redirect()->route('client.team.index')
            ->with('success', "Colaborador \"{$name}\" removido.");
    }

    private function authorizeTeamManagement(): User
    {
        $user = Auth::user();
        abort_unless($user->isCliente() && $user->hasPermission('users.create'), 403);
        return $user;
    }

    private function authorizeTeamAccess(User $target): User
    {
        $user = Auth::user();
        abort_unless($user->isCliente() || $user->isAdminGeral(), 403);
        abort_unless(
            $user->isAdminGeral() || ($target->isColaborador() && $target->client_id === $user->client_id),
            403,
            'Você não pode gerenciar este usuário.'
        );
        return $user;
    }
}
