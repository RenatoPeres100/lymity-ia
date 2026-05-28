<?php

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalRequestPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        return $user->isClientUser() && $user->hasPermission('approvals.view');
    }

    public function view(User $user, ApprovalRequest $approval): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('approvals.view')) {
            return $user->client_id && $approval->client_id === $user->client_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, ApprovalRequest $approval): bool
    {
        return $user->isAdminGeral();
    }

    public function approve(User $user, ApprovalRequest $approval): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('approvals.approve')) {
            return $user->client_id && $approval->client_id === $user->client_id;
        }
        return false;
    }

    public function reject(User $user, ApprovalRequest $approval): bool
    {
        return $this->approve($user, $approval);
    }

    public function comment(User $user, ApprovalRequest $approval): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('approvals.view')) {
            return $user->client_id && $approval->client_id === $user->client_id;
        }
        return false;
    }
}
