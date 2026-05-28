<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        return $user->isCliente() && $user->hasPermission('logs.view');
    }

    public function view(User $user, ActivityLog $log): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isCliente() && $user->hasPermission('logs.view')) {
            return $user->client_id && $log->client_id === $user->client_id;
        }
        return false;
    }
}
