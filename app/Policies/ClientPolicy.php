<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return $user->client_id === $client->id;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isAdminGeral();
    }

    public function disable(User $user, Client $client): bool
    {
        return $user->isAdminGeral();
    }

    public function activate(User $user, Client $client): bool
    {
        return $user->isAdminGeral();
    }

    public function archive(User $user, Client $client): bool
    {
        return $user->isAdminGeral();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isAdminGeral();
    }
}
