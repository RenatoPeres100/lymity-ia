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

    /** @deprecated — all non-client users are admin_geral in the new model */
    public function isAgencyUser(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function isClientUser(User $user): bool
    {
        return $user->isClientUser();
    }

    public function isAiEmployee(User $user): bool
    {
        return $user->isAiEmployee();
    }

    public function getCompanyId(User $user): ?int
    {
        if ($this->isGlobalAdmin($user)) return null;
        return $user->company_id;
    }

    public function getClientId(User $user): ?int
    {
        if ($this->isGlobalAdmin($user)) return null;
        return $user->client_id;
    }

    // ── Can-see checks ────────────────────────────────────────────────────────

    public function canSeeCompany(User $user, ?int $companyId): bool
    {
        if ($this->isGlobalAdmin($user)) return true;
        return false;
    }

    public function canSeeClient(User $user, ?int $clientId): bool
    {
        if ($this->isGlobalAdmin($user)) return true;
        if ($user->isClientUser()) {
            return $user->client_id && $user->client_id === $clientId;
        }
        return false;
    }

    public function canSeeUser(User $user, User $target): bool
    {
        if ($this->isGlobalAdmin($user)) return true;
        if ($user->isClientUser()) {
            return $user->client_id && $target->client_id === $user->client_id;
        }
        return false;
    }

    // ── Query scopes ──────────────────────────────────────────────────────────

    public function scopeCompanies(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;
        return $query->whereRaw('0 = 1');
    }

    public function scopeClients(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeUsers(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeBlogPosts(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeContentBriefs(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeSocialPosts(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeApprovals(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeActivityLogs(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeAiTasks(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        $table = $query->getModel()->getTable();

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where("{$table}.client_id", $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeAgentRoutines(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeFiles(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeBrandContexts(User $user, Builder $query): Builder
    {
        if ($this->isGlobalAdmin($user)) return $query;

        if ($user->isClientUser()) {
            if (!$user->client_id) return $query->whereRaw('0 = 1');
            return $query->where('client_id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }

    // ── Ownership ─────────────────────────────────────────────────────────────

    public function applyOwnershipOnCreate(User $user, array $data, string $modelClass = ''): array
    {
        if ($this->isGlobalAdmin($user)) {
            return $data;
        }

        if ($user->isClientUser()) {
            $data['client_id']  = $user->client_id;
            $data['company_id'] = optional(optional($user->client)->company)->id;
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
            // logging must never crash the app
        }
    }
}
