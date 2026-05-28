<?php

namespace App\Policies;

use App\Models\ContentBrief;
use App\Models\User;

class ContentBriefPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function view(User $user, ContentBrief $brief): bool
    {
        return $user->isAdminGeral();
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, ContentBrief $brief): bool
    {
        return $user->isAdminGeral();
    }

    public function delete(User $user, ContentBrief $brief): bool
    {
        return $user->isAdminGeral();
    }
}
