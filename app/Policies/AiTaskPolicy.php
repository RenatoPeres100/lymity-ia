<?php

namespace App\Policies;

use App\Models\AiTask;
use App\Models\Client;
use App\Models\User;

class AiTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function view(User $user, AiTask $task): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            if (!$user->company_id) return false;
            if ($task->client_id) {
                return Client::where('id', $task->client_id)
                    ->where('company_id', $user->company_id)
                    ->exists();
            }
            return $task->created_by === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function update(User $user, AiTask $task): bool
    {
        return $this->view($user, $task);
    }

    public function approve(User $user, AiTask $task): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) return $this->view($user, $task);
        return false;
    }
}
