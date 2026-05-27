<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        if (in_array($user->role, ['cliente_admin', 'cliente_colaborador'])) return false;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if (in_array($user->role, ['cliente_admin', 'cliente_colaborador'])) return false;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.view');
    }

    public function create(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.update');
    }

    public function disable(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.disable');
    }

    public function activate(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.activate');
    }

    public function archive(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->user_type === 'client') return false;

        return $user->hasPermission('clients.archive');
    }

    public function delete(User $user, Client $client): bool
    {
        // Delete means archive, not physical delete
        return $this->archive($user, $client);
    }
}
