<?php

namespace App\Services\Users;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    public function createUser(array $data, User $actor): User
    {
        if (!$this->canCreateRole($actor, $data['role'])) {
            throw ValidationException::withMessages(['role' => 'Você não tem permissão para criar usuários com esse perfil.']);
        }

        // Enforce ownership: agency users can only create users in their own company
        if (!$actor->isAdminGeral() && $actor->isAgencyUser()) {
            $data['company_id'] = $actor->company_id;
        }

        // Client admin can only create users in their own client
        if ($actor->isClientAdmin()) {
            $data['client_id']  = $actor->client_id;
            $data['company_id'] = optional(optional($actor->client)->company)->id;
        }

        $password = $data['password'] ?? Str::password(16);

        $user = User::create([
            'company_id'         => $data['company_id'] ?? null,
            'client_id'          => $data['client_id'] ?? null,
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($password),
            'role'               => $data['role'],
            'user_type'          => $data['user_type'],
            'job_title'          => $data['job_title'] ?? null,
            'status'             => $data['status'] ?? 'active',
            'email_verified_at'  => now(),
        ]);

        // Apply default permissions for this role
        $this->applyRolePreset($user);

        $this->registerLog('user_created', $user, $actor, [
            'role'      => $user->role,
            'user_type' => $user->user_type,
            'status'    => $user->status,
        ]);

        // Attach to user for single-display (not stored)
        $user->plain_password = isset($data['password']) ? null : $password;

        return $user;
    }

    public function updateUser(User $user, array $data, User $actor): User
    {
        if (!$this->canManage($actor, $user)) {
            throw ValidationException::withMessages(['user' => 'Você não tem permissão para editar este usuário.']);
        }

        if (isset($data['role']) && $data['role'] !== $user->role) {
            if (!$this->canCreateRole($actor, $data['role'])) {
                throw ValidationException::withMessages(['role' => 'Você não tem permissão para atribuir esse perfil.']);
            }
        }

        $fill = array_filter([
            'name'      => $data['name'] ?? null,
            'email'     => $data['email'] ?? null,
            'role'      => $data['role'] ?? null,
            'user_type' => $data['user_type'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'status'    => $data['status'] ?? null,
            'company_id'=> array_key_exists('company_id', $data) ? $data['company_id'] : null,
            'client_id' => array_key_exists('client_id', $data) ? $data['client_id'] : null,
        ], fn($v) => $v !== null);

        // Allow explicit null for FK fields
        if (array_key_exists('company_id', $data)) $fill['company_id'] = $data['company_id'];
        if (array_key_exists('client_id', $data))  $fill['client_id']  = $data['client_id'];

        $roleChanged = isset($data['role']) && $data['role'] !== $user->role;

        $user->update($fill);

        // Re-apply preset when role changes
        if ($roleChanged) {
            $this->applyRolePreset($user->fresh());
        }

        $this->registerLog('user_updated', $user, $actor, ['fields_changed' => array_keys($fill)]);

        return $user->fresh();
    }

    public function activate(User $user, User $actor): User
    {
        if (!$this->canManage($actor, $user)) {
            throw ValidationException::withMessages(['user' => 'Sem permissão.']);
        }

        $user->update(['status' => 'active']);
        $this->registerLog('user_activated', $user, $actor);

        return $user->fresh();
    }

    public function deactivate(User $user, User $actor): User
    {
        if ($actor->id === $user->id) {
            throw ValidationException::withMessages(['user' => 'Você não pode inativar sua própria conta.']);
        }

        if (!$this->canManage($actor, $user)) {
            throw ValidationException::withMessages(['user' => 'Sem permissão.']);
        }

        $user->update(['status' => 'inactive']);
        $this->registerLog('user_deactivated', $user, $actor);

        return $user->fresh();
    }

    public function block(User $user, User $actor): User
    {
        if ($actor->id === $user->id) {
            throw ValidationException::withMessages(['user' => 'Você não pode bloquear sua própria conta.']);
        }

        if (!$this->canManage($actor, $user)) {
            throw ValidationException::withMessages(['user' => 'Sem permissão.']);
        }

        $user->update(['status' => 'blocked']);
        $this->registerLog('user_blocked', $user, $actor);

        return $user->fresh();
    }

    public function resetPassword(User $user, string $password, User $actor): User
    {
        if (!$this->canManage($actor, $user)) {
            throw ValidationException::withMessages(['user' => 'Sem permissão.']);
        }

        $user->update(['password' => Hash::make($password)]);
        $this->registerLog('user_password_reset', $user, $actor);

        return $user->fresh();
    }

    public function syncPermissions(User $user, array $permissionIds, User $actor): User
    {
        if (!$actor->isAdminGeral() && !$actor->hasPermission('users.manage_permissions')) {
            throw ValidationException::withMessages(['user' => 'Sem permissão para gerenciar permissões.']);
        }

        $user->permissions()->sync($permissionIds);

        $this->registerLog('user_permissions_updated', $user, $actor, [
            'permission_ids' => $permissionIds,
        ]);

        return $user->fresh();
    }

    public function applyRolePreset(User $user): void
    {
        $keys = RolePermissionPreset::forRole($user->role);
        if (empty($keys)) {
            return;
        }

        $ids = \App\Models\Permission::whereIn('key', $keys)->pluck('id')->toArray();
        $user->permissions()->sync($ids);
    }

    public function resetToRolePreset(User $user, User $actor): User
    {
        if (!$this->canManage($actor, $user)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['user' => 'Sem permissão.']);
        }

        $this->applyRolePreset($user);

        $this->registerLog('user_permissions_updated', $user, $actor, [
            'action_detail' => 'reset_to_role_preset',
            'role'          => $user->role,
        ]);

        return $user->fresh();
    }

    public function canManage(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) {
            return true;
        }

        // target is admin_geral — only another admin_geral can manage
        if ($target->role === 'admin_geral') {
            return false;
        }

        if ($actor->role === 'agencia_admin') {
            // agency admin manages internal + client users, never admin_geral
            return in_array($target->user_type, ['agency', 'client', 'internal']);
        }

        if ($actor->role === 'cliente_admin') {
            // client admin only manages users of the same client
            return $target->user_type === 'client'
                && $target->client_id !== null
                && $target->client_id === $actor->client_id;
        }

        return false;
    }

    public function canCreateRole(User $actor, string $role): bool
    {
        if ($actor->isAdminGeral()) {
            return true;
        }

        $adminOnlyRoles = ['admin_geral'];
        if (in_array($role, $adminOnlyRoles)) {
            return false;
        }

        if ($actor->role === 'agencia_admin') {
            return in_array($role, [
                'agencia_operador', 'social_media', 'copywriter',
                'blog_writer', 'seo', 'designer', 'gestor_trafego',
                'cliente_admin', 'cliente_colaborador', 'viewer',
            ]);
        }

        if ($actor->role === 'cliente_admin' && $actor->hasPermission('users.create')) {
            return in_array($role, ['cliente_colaborador', 'viewer']);
        }

        return false;
    }

    public function registerLog(string $action, User $target, User $actor, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'      => $actor->id,
                'action'       => $action,
                'module'       => 'users',
                'level'        => 'info',
                'description'  => $this->logDescription($action, $target),
                'metadata'     => array_merge($metadata, ['target_user_id' => $target->id, 'target_email' => $target->email]),
                'subject_type' => User::class,
                'subject_id'   => $target->id,
                'ip_address'   => Request::ip(),
                'user_agent'   => Request::userAgent(),
            ]);
        } catch (\Throwable) {
            // Logging must never break the main operation
        }
    }

    private function logDescription(string $action, User $target): string
    {
        return match ($action) {
            'user_created'              => "Usuário {$target->name} ({$target->email}) criado.",
            'user_updated'              => "Usuário {$target->name} atualizado.",
            'user_activated'            => "Usuário {$target->name} ativado.",
            'user_deactivated'          => "Usuário {$target->name} inativado.",
            'user_blocked'              => "Usuário {$target->name} bloqueado.",
            'user_password_reset'       => "Senha de {$target->name} redefinida.",
            'user_permissions_updated'  => "Permissões de {$target->name} atualizadas.",
            default                     => "Ação {$action} em {$target->name}.",
        };
    }
}
