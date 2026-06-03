<?php

namespace App\Policies;

use App\Models\ProspectLead;
use App\Models\User;

class ProspectLeadPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        return $user->hasPermission('prospecting.view');
    }

    public function view(User $user, ProspectLead $lead): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.view')) return false;
        return $this->inScope($user, $lead);
    }

    public function create(User $user): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        return $user->hasPermission('prospecting.create');
    }

    public function update(User $user, ProspectLead $lead): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.update')) return false;
        return $this->inScope($user, $lead);
    }

    public function delete(User $user, ProspectLead $lead): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasPermission('prospecting.delete')) return false;
        return $this->inScope($user, $lead);
    }

    public function moveStage(User $user, ProspectLead $lead): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        if (!$user->hasAnyPermission(['prospecting.update', 'prospecting.pipeline.manage'])) return false;
        return $this->inScope($user, $lead);
    }

    public function archive(User $user, ProspectLead $lead): bool
    {
        return $this->update($user, $lead);
    }

    public function generateAi(User $user, ProspectLead $lead, string $type = 'analyze'): bool
    {
        if ($user->isClientUser() || $user->isAiEmployee()) return false;
        $permMap = [
            'analyze'         => 'prospecting.ai.analyze',
            'qualify'         => 'prospecting.ai.qualify',
            'generate_message' => 'prospecting.ai.generate_message',
            'suggest_next_action' => 'prospecting.ai.suggest_next_action',
            'summarize'       => 'prospecting.ai.analyze',
        ];
        $perm = $permMap[$type] ?? 'prospecting.ai.analyze';
        if (!$user->hasPermission($perm)) return false;
        return $this->inScope($user, $lead);
    }

    private function inScope(User $user, ProspectLead $lead): bool
    {
        if ($user->isAdminGeral()) return true;
        return $lead->company_id === $user->company_id;
    }
}
