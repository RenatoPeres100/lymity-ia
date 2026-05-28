<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return false;
        return $user->isAgencyUser();
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            return $user->company_id && $client->company_id === $user->company_id;
        }

        if ($user->isClientUser()) {
            return $user->client_id && $client->id === $user->client_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return false;
        return $user->isAgencyAdmin() || $user->hasPermission('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return false;

        if ($user->isAgencyUser()) {
            if ($client->company_id !== $user->company_id) return false;
            return $user->isAgencyAdmin() || $user->hasPermission('clients.update');
        }

        return false;
    }

    public function disable(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return false;

        if ($user->isAgencyUser()) {
            if ($client->company_id !== $user->company_id) return false;
            return $user->isAgencyAdmin() || $user->hasPermission('clients.disable');
        }

        return false;
    }

    public function activate(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return false;

        if ($user->isAgencyUser()) {
            if ($client->company_id !== $user->company_id) return false;
            return $user->isAgencyAdmin() || $user->hasPermission('clients.activate');
        }

        return false;
    }

    public function archive(User $user, Client $client): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser()) return false;

        if ($user->isAgencyUser()) {
            if ($client->company_id !== $user->company_id) return false;
            return $user->isAgencyAdmin() || $user->hasPermission('clients.archive');
        }

        return false;
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->archive($user, $client);
    }
}
