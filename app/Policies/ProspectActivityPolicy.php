<?php

namespace App\Policies;

use App\Models\ProspectActivity;
use App\Models\User;

class ProspectActivityPolicy
{
    public function create(User $user): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        return $user->hasPermission('prospecting.activities.create');
    }

    public function update(User $user, ProspectActivity $activity): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.activities.update')) return false;
        if ($user->isAdminGeral()) return true;
        return $activity->company_id === $user->company_id;
    }
}
