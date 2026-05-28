<?php

namespace App\Policies;

use App\Models\AgentRoutine;
use App\Models\User;

class AgentRoutinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function view(User $user, AgentRoutine $routine): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            return $user->company_id && $routine->company_id === $user->company_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyAdmin();
    }

    public function update(User $user, AgentRoutine $routine): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyAdmin()) {
            return $user->company_id && $routine->company_id === $user->company_id;
        }
        return false;
    }

    public function delete(User $user, AgentRoutine $routine): bool
    {
        return $this->update($user, $routine);
    }
}
