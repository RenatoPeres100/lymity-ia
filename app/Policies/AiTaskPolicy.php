<?php

namespace App\Policies;

use App\Models\AiTask;
use App\Models\User;

class AiTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function view(User $user, AiTask $task): bool
    {
        return $user->isAdminGeral();
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, AiTask $task): bool
    {
        return $user->isAdminGeral();
    }

    public function approve(User $user, AiTask $task): bool
    {
        return $user->isAdminGeral();
    }
}
