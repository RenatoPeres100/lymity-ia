<?php

namespace App\Policies;

use App\Models\ContentBrief;
use App\Models\User;

class ContentBriefPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function view(User $user, ContentBrief $brief): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            return $user->company_id && $brief->company_id === $user->company_id;
        }

        if ($user->isClientUser()) {
            return $user->client_id && $brief->client_id === $user->client_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function update(User $user, ContentBrief $brief): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            return $user->company_id && $brief->company_id === $user->company_id;
        }
        return false;
    }

    public function delete(User $user, ContentBrief $brief): bool
    {
        return $this->update($user, $brief);
    }
}
