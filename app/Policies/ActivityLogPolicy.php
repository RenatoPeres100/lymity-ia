<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser()
            || ($user->isClientAdmin() && $user->hasPermission('logs.view'));
    }

    public function view(User $user, ActivityLog $log): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            if (!$user->company_id) return false;
            if ($log->client_id) {
                return Client::where('id', $log->client_id)
                    ->where('company_id', $user->company_id)
                    ->exists();
            }
            if ($log->user_id) {
                $logUser = \App\Models\User::find($log->user_id);
                return $logUser && $logUser->company_id === $user->company_id;
            }
            return false;
        }

        if ($user->isClientUser() && $user->hasPermission('logs.view')) {
            return $user->client_id && $log->client_id === $user->client_id;
        }

        return false;
    }
}
