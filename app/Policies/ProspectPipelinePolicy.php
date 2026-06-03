<?php

namespace App\Policies;

use App\Models\ProspectPipeline;
use App\Models\User;

class ProspectPipelinePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        return $user->hasPermission('prospecting.pipeline.view');
    }

    public function view(User $user, ProspectPipeline $pipeline): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.pipeline.view')) return false;
        if ($user->isAdminGeral()) return true;
        return $pipeline->company_id === $user->company_id;
    }

    public function manage(User $user, ProspectPipeline $pipeline): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.pipeline.manage')) return false;
        if ($user->isAdminGeral()) return true;
        return $pipeline->company_id === $user->company_id;
    }
}
