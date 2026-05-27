<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPermissionsRequest;
use App\Models\Permission;
use App\Models\User;
use App\Services\Users\RolePermissionPreset;
use App\Services\Users\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserPermissionController extends Controller
{
    public function __construct(private UserManagementService $svc) {}

    public function edit(User $user): View
    {
        $this->authorize('managePermissions', $user);

        $user->load('permissions');
        $permissions  = Permission::orderBy('module')->orderBy('name')->get();
        $grouped      = $permissions->groupBy('module');
        $userPermIds  = $user->permissions->pluck('id')->toArray();
        $presetKeys   = RolePermissionPreset::forRole($user->role);
        $presetIds    = Permission::whereIn('key', $presetKeys)->pluck('id')->toArray();
        $presetLabel  = RolePermissionPreset::label($user->role);

        return view('admin.users.permissions', compact(
            'user', 'grouped', 'userPermIds', 'presetIds', 'presetLabel'
        ));
    }

    public function update(UpdateUserPermissionsRequest $request, User $user): RedirectResponse
    {
        $ids = $request->input('permissions', []);
        $this->svc->syncPermissions($user, $ids, Auth::user());

        return redirect()->route('admin.users.show', $user)->with('success', 'Permissões atualizadas com sucesso.');
    }

    public function reset(Request $request, User $user): RedirectResponse
    {
        $this->authorize('managePermissions', $user);
        $this->svc->resetToRolePreset($user, Auth::user());

        return redirect()->route('admin.users.permissions.edit', $user)
            ->with('success', "Permissões de {$user->name} redefinidas para o padrão do perfil {$user->role_label}.");
    }
}
