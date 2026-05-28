<?php

namespace App\Policies;

use App\Models\AgencyBrandContext;
use App\Models\User;

class BrandContextPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser();
    }

    public function view(User $user, AgencyBrandContext $context): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            return $user->company_id && $context->company_id === $user->company_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyAdmin();
    }

    public function update(User $user, AgencyBrandContext $context): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyAdmin()) {
            return $user->company_id && $context->company_id === $user->company_id;
        }
        return false;
    }
}
