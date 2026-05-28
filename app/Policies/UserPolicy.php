<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        // Admin sees all. Cliente sees own collaborators.
        return $actor->isAdminGeral()
            || ($actor->isCliente() && $actor->hasPermission('users.view'));
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) return true;
        if ($actor->isCliente()) {
            return $target->client_id === $actor->client_id || $actor->id === $target->id;
        }
        return $actor->id === $target->id;
    }

    public function create(User $actor): bool
    {
        // Admin creates any user. Cliente creates collaborators for own client.
        return $actor->isAdminGeral()
            || ($actor->isCliente() && $actor->hasPermission('users.create'));
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) return true;
        if ($target->role === 'admin_geral') return false;
        if ($actor->isCliente()) {
            return $target->client_id === $actor->client_id;
        }
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
        return $actor->isAdminGeral();
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function managePermissions(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        // Admin manages all. Cliente manages own collaborators' permissions.
        if ($actor->isAdminGeral()) return true;
        if ($actor->isCliente()) {
            return $target->isColaborador() && $target->client_id === $actor->client_id;
        }
        return false;
    }

    public function viewLogs(User $actor, User $target): bool
    {
        return $this->view($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        if ($target->role === 'admin_geral' && !$actor->isAdminGeral()) return false;
        if ($actor->isAdminGeral()) return true;
        if ($actor->isCliente()) {
            return $target->isColaborador() && $target->client_id === $actor->client_id;
        }
        return false;
    }
}
