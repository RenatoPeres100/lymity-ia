<?php

namespace App\Services\Auth;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccessScopeService
{
    public function currentUser(): ?User
    {
        return Auth::user();
    }

    public function isGlobalAdmin(User $user): bool
    {
        return $user->role === 'admin_geral';
    }

    public function isAgencyUser(User $user): bool
    {
        return in_array($user->role, [
            'agencia_admin', 'agencia_operador',
            'social_media', 'gestor_trafego', 'seo',
            'copywriter', 'designer', 'blog_writer',
        ]);
    }

    public function isClientUser(User $user): bool
    {
        return in_array($user->role, ['cliente_admin', 'cliente_colaborador'])
            || ($user->role === 'viewer' && $user->user_type === 'client');
    }

    public function isAiEmployee(User $user): bool
    {
        return $user->user_type === 'ai' || $user->role === 'ai_employee';
    }

    public function getCompanyId(User $user): ?int
    {
        if ($this->isGlobalAdmin($user)) return null;

        if ($this->isAgencyUser($user)) {
            return $user->company_id;
        }

        if ($this->isClientUser($user) && $user->client_id) {
            return optional($user->client)->company_id;
        }

        return null;
    }

    public function getClientId(User $user): ?int
    {
        if ($this->isGlobalAdmin($user)) return null;
        if ($this->isAgencyUser($user)) return null;

        return $user->client_id;
    }

    // ── Can-see checks ────────────────────────────────────────────────────────

    public function canSeeCompany(User $user, ?int $companyId): bool
    {
        if ($this->isGlobalAdmin($user)) return true;
        if ($this->isAgencyUser($user)) return $user->company_id && $user->company_id === $companyId;
        if ($this->isClientUser($user)) {
            return optional(optional($user->client)->company)->id === $companyId;
        }
        return false;
    }

    public function canSeeClient(User $user, ?int $clientId): bool
    {
        if ($this->isGlobalAdmin($user)) return true;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return false;
            return Client::where('id', $clientId)
                ->where('company_id', $user->company_id)
                ->exists();
        }

        if ($this->isClientUser($user)) {
            return $user->client_id && $user->client_id === $clientId;
        }

        return false;
    }

    public function canSeeUser(User $user, User $target): bool
    {
        if ($this->isGlobalAdmin($user)) return true;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return false;
            // Same company_id, or target belongs to client of same company
            if ($target->company_id === $user->company_id) return true;
            if ($target->client_id) {
                return Client::where('id', $target->client_id)
                    ->where('company_id', $user->company_id)
                    ->exists();
            }
            return false;
        }

        if ($this->isClientUser($user)) {
            return $user->client_id && $target->client_id === $user->client_id;
        }

        return false;
    }

    // ── Query scopes ──────────────────────────────────────────────────────────

    public function scopeCompanies(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            return $query->where('companies.id', $user->company_id);
        }

        if ($this->isClientUser($user) && $user->client_id) {
            $companyId = optional(optional($user->client)->company)->id;
            if ($companyId) return $query->where('companies.id', $companyId);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeClients(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeUsers(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');

            $clientIds = Client::where('company_id', $user->company_id)->pluck('id');

            return $query->where(function ($q) use ($user, $clientIds, $table) {
                $q->where("{$table}.company_id", $user->company_id)
                  ->orWhereIn("{$table}.client_id", $clientIds);
            });
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeBlogPosts(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeContentBriefs(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeSocialPosts(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeApprovals(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            $clientIds = Client::where('company_id', $user->company_id)->pluck('id');
            return $query->whereIn("{$table}.client_id", $clientIds);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeActivityLogs(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            $clientIds = Client::where('company_id', $user->company_id)->pluck('id');
            $userIds   = User::where(function ($q) use ($user, $clientIds) {
                $q->where('company_id', $user->company_id)
                  ->orWhereIn('client_id', $clientIds);
            })->pluck('id');

            return $query->where(function ($q) use ($table, $clientIds, $userIds) {
                $q->whereIn("{$table}.client_id", $clientIds)
                  ->orWhereIn("{$table}.user_id", $userIds);
            });
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeAiTasks(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            $clientIds = Client::where('company_id', $user->company_id)->pluck('id');
            return $query->where(function ($q) use ($table, $user, $clientIds) {
                $q->whereIn("{$table}.client_id", $clientIds)
                  ->orWhere("{$table}.created_by", $user->id);
            });
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeAgentRoutines(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeFiles(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeBrandContexts(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($this->isAgencyUser($user)) {
            if (!$user->company_id) return $query->whereRaw('0 = 1');
            return $query->where('company_id', $user->company_id);
        }

        if ($this->isClientUser($user)) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    // ── Store helpers ─────────────────────────────────────────────────────────

    /**
     * Apply ownership fields (company_id, client_id) when creating a record.
     * Prevents users from injecting arbitrary IDs via request.
     */
    public function applyOwnershipOnCreate(User $user, array $data, string $modelClass = ''): array
    {
        if ($this->isGlobalAdmin($user)) {
            return $data;
        }

        if ($this->isAgencyUser($user)) {
            $data['company_id'] = $user->company_id;

            // Validate that provided client_id belongs to same company
            if (!empty($data['client_id'])) {
                $valid = Client::where('id', $data['client_id'])
                    ->where('company_id', $user->company_id)
                    ->exists();
                if (!$valid) unset($data['client_id']);
            }

            return $data;
        }

        if ($this->isClientUser($user)) {
            $data['client_id']  = $user->client_id;
            $data['company_id'] = optional(optional($user->client)->company)->id;
            return $data;
        }

        return $data;
    }

    // ── Access-denied logger ──────────────────────────────────────────────────

    public function logDenied(User $user, string $reason, string $route = '', ?string $targetType = null, ?int $targetId = null): void
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id'      => $user->id,
                'action'       => $reason,
                'module'       => 'access_control',
                'level'        => 'warning',
                'description'  => "Acesso negado: {$reason} — Rota: {$route}",
                'metadata'     => [
                    'reason'      => $reason,
                    'route'       => $route,
                    'ip'          => request()->ip(),
                    'user_agent'  => request()->userAgent(),
                    'target_type' => $targetType,
                    'target_id'   => $targetId,
                ],
                'ip_address'   => request()->ip(),
                'user_agent'   => request()->userAgent(),
                'subject_type' => $targetType,
                'subject_id'   => $targetId,
            ]);
        } catch (\Throwable) {
            // never crash on logging failure
        }
    }
}
