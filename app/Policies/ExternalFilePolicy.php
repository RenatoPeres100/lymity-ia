<?php

namespace App\Policies;

use App\Models\ExternalFile;
use App\Models\User;

class ExternalFilePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        return $user->isClientUser() && $user->hasPermission('files.view');
    }

    public function view(User $user, ExternalFile $file): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isClientUser() && $user->hasPermission('files.view')) {
            return $user->client_id && $file->client_id === $user->client_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isAdminGeral()) return true;
        return $user->isClientUser() && $user->hasPermission('files.create');
    }

    public function delete(User $user, ExternalFile $file): bool
    {
        if ($user->isAdminGeral()) return true;
        if ($user->isCliente()) {
            return $user->client_id && $file->client_id === $user->client_id;
        }
        return $file->uploaded_by === $user->id;
    }
}
