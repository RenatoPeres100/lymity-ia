<?php

namespace App\Services\Prospecting;

use App\Models\ProspectActivity;
use App\Models\ProspectLead;
use App\Models\ProspectNote;
use App\Models\ProspectStage;
use App\Models\User;

class ProspectingService
{
    public function __construct(
        private ProspectingLogService $logger,
        private ProspectingPipelineService $pipelineService,
    ) {}

    public function createLead(array $data, User $user): ProspectLead
    {
        $pipeline = $this->pipelineService->getDefaultForUser($user);
        $firstStage = $pipeline?->stages()->active()->orderBy('position')->first();

        $lead = ProspectLead::create(array_merge($data, [
            'company_id'          => $user->isAdminGeral() ? ($data['company_id'] ?? null) : $user->company_id,
            'prospect_pipeline_id' => $data['prospect_pipeline_id'] ?? $pipeline?->id,
            'prospect_stage_id'   => $data['prospect_stage_id'] ?? $firstStage?->id,
            'owner_id'            => $data['owner_id'] ?? $user->id,
            'status'              => 'open',
        ]));

        $this->logger->log('prospect.lead.created', $lead, $user, [
            'name'    => $lead->name,
            'company' => $lead->company_name,
        ]);

        return $lead;
    }

    public function updateLead(ProspectLead $lead, array $data, User $user): ProspectLead
    {
        $lead->update($data);

        $this->logger->log('prospect.lead.updated', $lead, $user, [
            'name' => $lead->name,
        ]);

        return $lead->fresh();
    }

    public function archiveLead(ProspectLead $lead, User $user): ProspectLead
    {
        $lead->update(['status' => 'archived']);

        $this->logger->log('prospect.lead.archived', $lead, $user);

        return $lead->fresh();
    }

    public function moveStage(ProspectLead $lead, ProspectStage $stage, User $user): ProspectLead
    {
        $oldStage = $lead->stage?->name ?? '—';

        $lead->update([
            'prospect_stage_id' => $stage->id,
            'status' => $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : 'open'),
        ]);

        $this->logger->log('prospect.lead.stage_moved', $lead, $user, [
            'from_stage' => $oldStage,
            'to_stage'   => $stage->name,
        ]);

        return $lead->fresh();
    }

    public function createActivity(ProspectLead $lead, array $data, User $user): ProspectActivity
    {
        $activity = ProspectActivity::create(array_merge($data, [
            'prospect_lead_id' => $lead->id,
            'company_id'       => $lead->company_id,
            'user_id'          => $user->id,
            'status'           => $data['status'] ?? 'pending',
        ]));

        $this->logger->log('prospect.activity.created', $activity, $user, [
            'lead'  => $lead->name,
            'title' => $activity->title,
        ]);

        return $activity;
    }

    public function completeActivity(ProspectActivity $activity, ?string $outcome, User $user): ProspectActivity
    {
        $activity->update([
            'status'       => 'done',
            'completed_at' => now(),
            'outcome'      => $outcome,
        ]);

        $this->logger->log('prospect.activity.completed', $activity, $user);

        return $activity->fresh();
    }

    public function cancelActivity(ProspectActivity $activity, User $user): ProspectActivity
    {
        $activity->update(['status' => 'canceled']);

        $this->logger->log('prospect.activity.canceled', $activity, $user);

        return $activity->fresh();
    }

    public function createNote(ProspectLead $lead, array $data, User $user): ProspectNote
    {
        $note = ProspectNote::create([
            'prospect_lead_id' => $lead->id,
            'company_id'       => $lead->company_id,
            'user_id'          => $user->id,
            'note'             => $data['note'],
            'visibility'       => $data['visibility'] ?? 'internal',
        ]);

        $this->logger->log('prospect.note.created', $note, $user, [
            'lead' => $lead->name,
        ]);

        return $note;
    }

    private function getDefaultForUser(User $user): ?\App\Models\ProspectPipeline
    {
        return $this->pipelineService->getDefaultForUser($user);
    }
}
