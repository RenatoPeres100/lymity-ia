<?php

namespace App\Policies;

use App\Models\ProspectMessageSuggestion;
use App\Models\User;

class ProspectAiPolicy
{
    public function approveMessage(User $user, ProspectMessageSuggestion $msg): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.update')) return false;
        if ($user->isAdminGeral()) return true;
        return $msg->company_id === $user->company_id;
    }

    public function markUsed(User $user, ProspectMessageSuggestion $msg): bool
    {
        return $this->approveMessage($user, $msg);
    }
}
