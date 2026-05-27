<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdminGeral()
            || in_array($actor->role, ['agencia_admin', 'agencia_operador'])
            || ($actor->role === 'cliente_admin' && $actor->hasPermission('users.view'));
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) return true;
        if (in_array($actor->role, ['agencia_admin', 'agencia_operador'])) return true;
        if ($actor->role === 'cliente_admin' && $target->client_id === $actor->client_id) return true;
        return false;
    }

    public function create(User $actor): bool
    {
        return $actor->isAdminGeral()
            || $actor->role === 'agencia_admin'
            || ($actor->role === 'cliente_admin' && $actor->hasPermission('users.create'));
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) return true;
        if ($target->role === 'admin_geral') return false;
        if ($actor->role === 'agencia_admin') return true;
        if ($actor->role === 'cliente_admin' && $target->client_id === $actor->client_id) return true;
        return false;
    }

    public function activate(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function deactivate(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        return $this->update($actor, $target);
    }

    public function block(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        if (!$actor->isAdminGeral() && $target->role === 'admin_geral') return false;
        return $actor->isAdminGeral() || $actor->role === 'agencia_admin';
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function managePermissions(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        return $actor->isAdminGeral()
            || ($actor->role === 'agencia_admin' && $actor->hasPermission('users.manage_permissions'));
    }

    public function viewLogs(User $actor, User $target): bool
    {
        return $this->view($actor, $target);
    }
}
