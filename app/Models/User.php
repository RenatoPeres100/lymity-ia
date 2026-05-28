<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id', 'client_id', 'name', 'email', 'password',
        'role', 'user_type', 'job_title', 'status', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    /** Valid human roles in the system. */
    public const ROLES = ['admin_geral', 'cliente', 'colaborador'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'requested_by');
    }

    public function approvalActions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }

    public function approvalComments(): HasMany
    {
        return $this->hasMany(ApprovalComment::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function createdSocialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'created_by');
    }

    public function approvedSocialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'approved_by');
    }

    public function createdProposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'created_by');
    }

    public function approvedProposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'approved_by');
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(ExternalFile::class, 'uploaded_by');
    }

    public function aiProviderCalls(): HasMany
    {
        return $this->hasMany(AiProviderCall::class);
    }

    // ── Role checks ───────────────────────────────────────────────────────────

    public function isAdminGeral(): bool
    {
        return $this->role === 'admin_geral';
    }

    /** Main user of a client company — can manage own environment. */
    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }

    /** Team member created by a Cliente — limited permissions. */
    public function isColaborador(): bool
    {
        return $this->role === 'colaborador';
    }

    /** Any user that belongs to the client area (cliente or colaborador). */
    public function isClientUser(): bool
    {
        return in_array($this->role, ['cliente', 'colaborador']);
    }

    public function isAiEmployee(): bool
    {
        return $this->user_type === 'ai' || $this->role === 'ai_employee';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ── Access helpers ────────────────────────────────────────────────────────

    public function canAccessAdminPanel(): bool
    {
        if (!$this->isActive() || $this->isAiEmployee()) return false;
        return $this->isAdminGeral();
    }

    public function canAccessClientPanel(): bool
    {
        if (!$this->isActive() || $this->isAiEmployee()) return false;
        return $this->isClientUser() || $this->isAdminGeral();
    }

    public function belongsToCompany(?int $companyId): bool
    {
        return $companyId && $this->company_id === $companyId;
    }

    public function belongsToClient(?int $clientId): bool
    {
        return $clientId && $this->client_id === $clientId;
    }

    // ── Permissions ───────────────────────────────────────────────────────────

    public function hasPermission(string $key): bool
    {
        if ($this->isAdminGeral()) return true;

        return $this->permissions()->where('key', $key)->exists();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeVisibleTo($query, User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeUsers($user, $query);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin_geral'  => 'Admin Geral',
            'cliente'      => 'Cliente',
            'colaborador'  => 'Colaborador',
            'ai_employee'  => 'Funcionário IA',
            default        => $this->role ?? '—',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Ativo',
            'inactive' => 'Inativo',
            'blocked'  => 'Bloqueado',
            default    => $this->status ?? '-',
        };
    }

    // ── Legacy aliases (backward compat for any remaining code) ───────────────

    /** @deprecated Use isAdminGeral() */
    public function isAgencyUser(): bool { return $this->isAdminGeral(); }

    /** @deprecated Use isAdminGeral() */
    public function isAgencyAdmin(): bool { return $this->isAdminGeral(); }

    /** @deprecated Use isCliente() */
    public function isClientAdmin(): bool { return $this->isCliente(); }

    /** @deprecated Use isColaborador() */
    public function isViewer(): bool { return $this->isColaborador(); }
}
