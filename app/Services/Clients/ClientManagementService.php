<?php

namespace App\Services\Clients;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class ClientManagementService
{
    public function createClient(array $data, User $actor): Client
    {
        $data = $this->normalizeData($data);

        // Enforce ownership: agency users can only create under their own company
        if (!$actor->isAdminGeral() && $actor->isAgencyUser()) {
            $data['company_id'] = $actor->company_id;
        }

        if (empty($data['company_id'])) {
            $data['company_id'] = Company::first()?->id;
        }

        $data['status'] = $data['status'] ?? 'active';

        $client = Client::create($data);

        $this->registerLog('client.created', $client, $actor, [
            'name'    => $client->name,
            'segment' => $client->segment,
            'status'  => $client->status,
        ]);

        return $client;
    }

    public function updateClient(Client $client, array $data, User $actor): Client
    {
        if (!$this->canManage($actor, $client)) {
            throw ValidationException::withMessages(['client' => 'Sem permissão para editar este cliente.']);
        }

        $data   = $this->normalizeData($data);
        $before = $client->only(array_keys($data));

        $client->update($data);

        $this->registerLog('client.updated', $client, $actor, [
            'before' => $before,
            'after'  => $client->fresh()->only(array_keys($data)),
        ]);

        return $client->fresh();
    }

    public function disable(Client $client, User $actor): Client
    {
        if (!$this->canManage($actor, $client)) {
            throw ValidationException::withMessages(['client' => 'Sem permissão.']);
        }

        $client->update(['status' => 'inactive']);
        $this->registerLog('client.disabled', $client, $actor);

        return $client->fresh();
    }

    public function activate(Client $client, User $actor): Client
    {
        if (!$this->canManage($actor, $client)) {
            throw ValidationException::withMessages(['client' => 'Sem permissão.']);
        }

        $client->update(['status' => 'active']);
        $this->registerLog('client.activated', $client, $actor);

        return $client->fresh();
    }

    public function archive(Client $client, User $actor): Client
    {
        if (!$this->canManage($actor, $client)) {
            throw ValidationException::withMessages(['client' => 'Sem permissão.']);
        }

        $client->update(['status' => 'archived']);
        $this->registerLog('client.archived', $client, $actor, ['name' => $client->name]);

        return $client->fresh();
    }

    public function canManage(User $actor, Client $client): bool
    {
        if ($actor->isAdminGeral()) return true;
        if ($actor->isAgencyUser()) {
            return $actor->company_id && $client->company_id === $actor->company_id;
        }
        return false;
    }

    public function normalizeData(array $data): array
    {
        if (!empty($data['instagram'])) {
            $data['instagram'] = '@' . ltrim(trim($data['instagram']), '@');
        }

        if (!empty($data['tax_id'])) {
            $digits = preg_replace('/[^\d]/', '', $data['tax_id']);
            if ($digits) $data['tax_id'] = $digits;
        }

        if (!empty($data['whatsapp'])) {
            $data['whatsapp'] = preg_replace('/[\s\-\(\)]/', '', $data['whatsapp']);
        }

        if (!empty($data['website']) && !preg_match('/^https?:\/\//', $data['website'])) {
            $data['website'] = 'https://' . $data['website'];
        }

        return $data;
    }

    public function registerLog(string $action, Client $client, User $actor, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'      => $actor->id,
                'client_id'    => $client->id,
                'action'       => $action,
                'module'       => 'clients',
                'level'        => 'info',
                'description'  => $this->logDescription($action, $client),
                'metadata'     => array_merge($metadata, ['client_id' => $client->id]),
                'subject_type' => Client::class,
                'subject_id'   => $client->id,
                'ip_address'   => Request::ip(),
                'user_agent'   => Request::userAgent(),
            ]);
        } catch (\Throwable) {
            // Logging must never break the main operation
        }
    }

    private function logDescription(string $action, Client $client): string
    {
        return match ($action) {
            'client.created'  => "Cliente {$client->name} criado.",
            'client.updated'  => "Cliente {$client->name} atualizado.",
            'client.disabled' => "Cliente {$client->name} desativado.",
            'client.activated'=> "Cliente {$client->name} ativado.",
            'client.archived' => "Cliente {$client->name} arquivado.",
            default           => "Ação {$action} em {$client->name}.",
        };
    }
}
