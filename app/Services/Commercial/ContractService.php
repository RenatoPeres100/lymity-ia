<?php

namespace App\Services\Commercial;

use App\Models\ActivityLog;
use App\Models\ClientContract;
use App\Models\User;

class ContractService
{
    public function createContract(array $data, User $user): ClientContract
    {
        $contract = ClientContract::create([
            'client_id'   => $data['client_id'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path'   => $data['file_path'] ?? null,
            'status'      => $data['status'] ?? 'draft',
        ]);

        $this->log($user, 'contract_created', "Contrato criado: {$contract->title}", $contract->client_id);

        return $contract;
    }

    public function markPendingSignature(ClientContract $contract, User $user): ClientContract
    {
        $contract->update(['status' => 'pending_signature']);
        $this->log($user, 'contract_pending_signature', "Contrato aguardando assinatura: {$contract->title}", $contract->client_id);
        return $contract->fresh();
    }

    public function markSigned(ClientContract $contract, User $user): ClientContract
    {
        $contract->update(['status' => 'signed', 'signed_at' => now()]);
        $this->log($user, 'contract_signed', "Contrato assinado: {$contract->title}", $contract->client_id);
        return $contract->fresh();
    }

    public function cancel(ClientContract $contract, User $user): ClientContract
    {
        $contract->update(['status' => 'canceled']);
        $this->log($user, 'contract_canceled', "Contrato cancelado: {$contract->title}", $contract->client_id);
        return $contract->fresh();
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
