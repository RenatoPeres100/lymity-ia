<?php

namespace App\Policies;

use App\Models\AgentRoutine;
use App\Models\User;

class AgentRoutinePolicy
{
    public function viewAny(User $user): bool   { return $user->isAdminGeral(); }
    public function view(User $user, AgentRoutine $r): bool { return $user->isAdminGeral(); }
    public function create(User $user): bool    { return $user->isAdminGeral(); }
    public function update(User $user, AgentRoutine $r): bool { return $user->isAdminGeral(); }
    public function delete(User $user, AgentRoutine $r): bool { return $user->isAdminGeral(); }
}
