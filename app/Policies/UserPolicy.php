<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdminGeral()
            || $actor->isAgencyUser()
            || ($actor->isClientAdmin() && $actor->hasPermission('users.view'));
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) return true;

        if ($actor->isAgencyUser()) {
            if (!$actor->company_id) return false;
            if ($target->company_id === $actor->company_id) return true;
            if ($target->client_id) {
                return Client::where('id', $target->client_id)
                    ->where('company_id', $actor->company_id)
                    ->exists();
            }
            return false;
        }

        if ($actor->isClientAdmin()) {
            if ($target->client_id && $target->client_id === $actor->client_id) return true;
            return $actor->id === $target->id;
        }

        return $actor->id === $target->id;
    }

    public function create(User $actor): bool
    {
        return $actor->isAdminGeral()
            || $actor->isAgencyAdmin()
            || ($actor->isClientAdmin() && $actor->hasPermission('users.create'));
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->isAdminGeral()) return true;
        if ($target->role === 'admin_geral') return false;

        if ($actor->isAgencyAdmin()) {
            if (!$actor->company_id) return false;
            if ($target->company_id === $actor->company_id) return true;
            if ($target->client_id) {
                return Client::where('id', $target->client_id)
                    ->where('company_id', $actor->company_id)
                    ->exists();
            }
            return false;
        }

        if ($actor->isClientAdmin()) {
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
        return $actor->isAdminGeral() || $actor->isAgencyAdmin();
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function managePermissions(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        return $actor->isAdminGeral()
            || ($actor->isAgencyAdmin() && $actor->hasPermission('users.manage_permissions'));
    }

    public function viewLogs(User $actor, User $target): bool
    {
        return $this->view($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        if ($target->role === 'admin_geral' && !$actor->isAdminGeral()) return false;
        return $actor->isAdminGeral() || $actor->isAgencyAdmin();
    }
}
