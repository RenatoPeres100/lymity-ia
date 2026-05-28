<?php

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\User;

class ApprovalRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser() || $user->isClientUser();
    }

    public function view(User $user, ApprovalRequest $approval): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            if (!$user->company_id) return false;
            if (!$approval->client_id) return true;
            return Client::where('id', $approval->client_id)
                ->where('company_id', $user->company_id)
                ->exists();
        }

        if ($user->isClientUser()) {
            return $user->client_id && $approval->client_id === $user->client_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function update(User $user, ApprovalRequest $approval): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            if (!$user->company_id || !$approval->client_id) return $user->isAdminGeral();
            return Client::where('id', $approval->client_id)
                ->where('company_id', $user->company_id)
                ->exists();
        }
        return false;
    }

    public function approve(User $user, ApprovalRequest $approval): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            if (!$user->company_id) return false;
            if (!$approval->client_id) return true;
            return Client::where('id', $approval->client_id)
                ->where('company_id', $user->company_id)
                ->exists();
        }

        if ($user->isClientAdmin()) {
            return $user->client_id && $approval->client_id === $user->client_id
                && $user->hasPermission('approvals.approve');
        }

        if ($user->isClientUser()) {
            return $user->client_id && $approval->client_id === $user->client_id
                && $user->hasPermission('approvals.approve');
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
        if ($user->isAgencyUser()) {
            if (!$user->company_id) return false;
            if (!$approval->client_id) return true;
            return Client::where('id', $approval->client_id)
                ->where('company_id', $user->company_id)
                ->exists();
        }
        if ($user->isClientUser()) {
            return $user->client_id && $approval->client_id === $user->client_id;
        }
        return false;
    }
}
