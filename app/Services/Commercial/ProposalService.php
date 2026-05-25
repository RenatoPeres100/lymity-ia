<?php

namespace App\Services\Commercial;

use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use App\Services\Ai\MockAiProvider;
use App\Services\Approval\ApprovalService;

class ProposalService
{
    public function __construct(
        protected ApprovalService $approvalService,
        protected MockAiProvider  $mockAi,
    ) {}

    public function createProposal(array $data, User $user): Proposal
    {
        $proposal = Proposal::create([
            'client_id'   => $data['client_id'],
            'created_by'  => $user->id,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'terms'       => $data['terms'] ?? null,
            'status'      => 'draft',
        ]);

        $this->syncItems($proposal, $data['items'] ?? []);

        $this->log($user, 'proposal_created', "Proposta criada: {$proposal->title}", $proposal->client_id);

        return $proposal->fresh();
    }

    public function updateProposal(Proposal $proposal, array $data, User $user): Proposal
    {
        $proposal->update([
            'client_id'   => $data['client_id'] ?? $proposal->client_id,
            'title'       => $data['title'] ?? $proposal->title,
            'description' => $data['description'] ?? $proposal->description,
            'valid_until' => $data['valid_until'] ?? $proposal->valid_until,
            'terms'       => $data['terms'] ?? $proposal->terms,
        ]);

        if (isset($data['items'])) {
            $proposal->items()->delete();
            $this->syncItems($proposal, $data['items']);
        }

        $this->log($user, 'proposal_updated', "Proposta atualizada: {$proposal->title}", $proposal->client_id);

        return $proposal->fresh();
    }

    public function recalculateTotal(Proposal $proposal): Proposal
    {
        return $proposal->recalculateTotal();
    }

    public function sendToInternalApproval(Proposal $proposal, User $user): ApprovalRequest
    {
        $proposal->update(['status' => 'pending_approval']);

        $approval = $this->approvalService->createApproval([
            'client_id'       => $proposal->client_id,
            'requested_by'    => $user->id,
            'ai_employee_id'  => $proposal->ai_employee_id,
            'approvable_type' => Proposal::class,
            'approvable_id'   => $proposal->id,
            'approval_type'   => 'proposal',
            'title'           => "Aprovar proposta: {$proposal->title}",
            'sensitive_level' => 'medium',
            'payload'         => [
                'total_amount' => $proposal->total_amount,
                'valid_until'  => $proposal->valid_until?->toDateString(),
                'terms'        => $proposal->terms,
                'items'        => $proposal->items->map(fn($i) => [
                    'name'        => $i->name,
                    'quantity'    => $i->quantity,
                    'unit_price'  => $i->unit_price,
                    'total_price' => $i->total_price,
                ])->toArray(),
            ],
        ]);

        $this->log($user, 'proposal_sent_to_approval', "Proposta enviada para aprovação: {$proposal->title}", $proposal->client_id);

        return $approval;
    }

    public function approveInternally(Proposal $proposal, User $user): Proposal
    {
        $proposal->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->log($user, 'proposal_approved', "Proposta aprovada internamente: {$proposal->title}", $proposal->client_id);

        return $proposal->fresh();
    }

    public function sendToClient(Proposal $proposal, User $user): Proposal
    {
        if ($proposal->status !== 'approved') {
            throw new \RuntimeException('Proposta precisa estar aprovada antes de ser enviada ao cliente.');
        }

        $proposal->update(['status' => 'sent']);

        $this->log($user, 'proposal_sent_to_client', "Proposta enviada ao cliente: {$proposal->title}", $proposal->client_id);

        return $proposal->fresh();
    }

    public function acceptByClient(Proposal $proposal, User $user, ?string $notes = null): Proposal
    {
        $proposal->update(['status' => 'accepted']);

        $this->log($user, 'proposal_accepted', "Proposta aceita pelo cliente: {$proposal->title}", $proposal->client_id);

        if ($notes) {
            ActivityLog::create([
                'user_id'     => $user->id,
                'client_id'   => $proposal->client_id,
                'action'      => 'proposal_accepted_note',
                'module'      => 'commercial',
                'description' => "Nota do cliente ao aceitar: {$notes}",
            ]);
        }

        return $proposal->fresh();
    }

    public function rejectByClient(Proposal $proposal, User $user, ?string $notes = null): Proposal
    {
        $proposal->update(['status' => 'rejected']);

        $this->log($user, 'proposal_rejected', "Proposta rejeitada pelo cliente: {$proposal->title}", $proposal->client_id);

        if ($notes) {
            ActivityLog::create([
                'user_id'     => $user->id,
                'client_id'   => $proposal->client_id,
                'action'      => 'proposal_rejected_note',
                'module'      => 'commercial',
                'description' => "Nota do cliente ao rejeitar: {$notes}",
            ]);
        }

        return $proposal->fresh();
    }

    public function generateWithAi(array $data, User $user): Proposal|AiTask
    {
        $aiEmployee = AiEmployee::where('role_key', 'copywriter')
            ->orWhere('name', 'like', '%Copywriter%')
            ->first()
            ?? AiEmployee::first();

        $task = AiTask::create([
            'client_id'      => $data['client_id'] ?? null,
            'ai_employee_id' => $aiEmployee?->id,
            'created_by'     => $user->id,
            'task_type'      => 'generate_proposal',
            'title'          => $data['title'] ?? 'Proposta gerada por IA',
            'description'    => $data['description'] ?? null,
            'status'         => 'running',
        ]);

        $output = $this->mockAi->generate($task);
        $parsed = json_decode($output, true) ?? [];

        $task->update(['status' => 'completed', 'output' => $output, 'finished_at' => now()]);

        $proposal = Proposal::create([
            'client_id'      => $data['client_id'] ?? null,
            'created_by'     => $user->id,
            'ai_employee_id' => $aiEmployee?->id,
            'title'          => $parsed['title'] ?? ($data['title'] ?? 'Proposta IA'),
            'description'    => $parsed['description'] ?? null,
            'terms'          => $parsed['terms'] ?? null,
            'valid_until'    => now()->addDays(30)->toDateString(),
            'status'         => 'draft',
        ]);

        foreach ($parsed['items'] ?? [] as $item) {
            $qty   = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            ProposalItem::create([
                'proposal_id' => $proposal->id,
                'name'        => $item['name'] ?? 'Item',
                'description' => $item['description'] ?? null,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total_price' => round($qty * $price, 2),
            ]);
        }

        $proposal->recalculateTotal();

        $this->log($user, 'proposal_created', "Proposta IA gerada: {$proposal->title}", $proposal->client_id);

        return $proposal->fresh();
    }

    private function syncItems(Proposal $proposal, array $items): void
    {
        foreach ($items as $item) {
            $qty   = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            ProposalItem::create([
                'proposal_id' => $proposal->id,
                'name'        => $item['name'],
                'description' => $item['description'] ?? null,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total_price' => round($qty * $price, 2),
            ]);
        }

        $proposal->recalculateTotal();
    }

    private function log(User $user, string $action, string $description, ?int $clientId = null): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }
        ActivityLog::create([
            'user_id'     => $user->id,
            'client_id'   => $clientId,
            'action'      => $action,
            'module'      => 'commercial',
            'description' => $description,
        ]);
    }
}
