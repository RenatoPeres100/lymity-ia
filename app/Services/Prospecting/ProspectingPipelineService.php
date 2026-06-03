<?php

namespace App\Services\Prospecting;

use App\Models\Company;
use App\Models\ProspectLead;
use App\Models\ProspectPipeline;
use App\Models\ProspectStage;
use App\Models\User;
use Database\Seeders\ProspectingSeeder;

class ProspectingPipelineService
{
    public function getDefaultPipelineForCompany(?Company $company): ?ProspectPipeline
    {
        if (!$company) {
            return ProspectPipeline::where('is_default', true)->with('stages')->first();
        }

        return ProspectPipeline::where('company_id', $company->id)
            ->where('is_default', true)
            ->with('stages')
            ->first();
    }

    public function installDefaultPipeline(?Company $company): ProspectPipeline
    {
        $seeder = new ProspectingSeeder();
        return $seeder->installForCompany($company ?? Company::first(), false);
    }

    public function getStages(ProspectPipeline $pipeline): \Illuminate\Database\Eloquent\Collection
    {
        return $pipeline->stages()->active()->orderBy('position')->get();
    }

    public function getKanbanData(User $user): array
    {
        $pipeline = $this->getDefaultForUser($user);
        if (!$pipeline) return [];

        $stages = $this->getStages($pipeline);

        return $stages->map(function (ProspectStage $stage) use ($user) {
            $leads = ProspectLead::visibleTo($user)
                ->where('prospect_stage_id', $stage->id)
                ->where('status', 'open')
                ->with('owner')
                ->orderByDesc('updated_at')
                ->get();

            return [
                'stage' => $stage,
                'leads' => $leads,
                'count' => $leads->count(),
            ];
        })->toArray();
    }

    public function getDefaultForUser(User $user): ?ProspectPipeline
    {
        $query = ProspectPipeline::with('stages')->where('is_default', true);

        if (!$user->isAdminGeral()) {
            $query->where('company_id', $user->company_id);
        }

        return $query->first();
    }
}
