<?php

namespace App\Policies;

use App\Models\ExternalFile;
use App\Models\User;

class ExternalFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser() || $user->isClientUser();
    }

    public function view(User $user, ExternalFile $file): bool
    {
        if ($user->isAdminGeral()) return true;

        if ($user->isAgencyUser()) {
            return $user->company_id && $file->company_id === $user->company_id;
        }

        if ($user->isClientUser()) {
            return $user->client_id && $file->client_id === $user->client_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isAgencyUser() || $user->isClientUser();
    }

    public function delete(User $user, ExternalFile $file): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isAgencyUser()) {
            return $user->company_id && $file->company_id === $user->company_id;
        }
        if ($user->isClientAdmin()) {
            return $user->client_id && $file->client_id === $user->client_id;
        }
        return $file->uploaded_by === $user->id;
    }
}
