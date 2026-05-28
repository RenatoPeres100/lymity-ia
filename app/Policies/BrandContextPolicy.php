<?php

namespace App\Policies;

use App\Models\AgencyBrandContext;
use App\Models\User;

class BrandContextPolicy
{
    public function viewAny(User $user): bool   { return $user->isAdminGeral(); }
    public function view(User $user, AgencyBrandContext $c): bool { return $user->isAdminGeral(); }
    public function create(User $user): bool    { return $user->isAdminGeral(); }
    public function update(User $user, AgencyBrandContext $c): bool { return $user->isAdminGeral(); }
}
