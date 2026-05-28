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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

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

    public function isAdminGeral(): bool
    {
        return $this->role === 'admin_geral';
    }

    public function isAgencyAdmin(): bool
    {
        return $this->role === 'agencia_admin';
    }

    public function isAgencyUser(): bool
    {
        return in_array($this->role, [
            'agencia_admin', 'agencia_operador',
            'social_media', 'gestor_trafego', 'seo',
            'copywriter', 'designer', 'blog_writer',
        ]);
    }

    public function isClientAdmin(): bool
    {
        return $this->role === 'cliente_admin';
    }

    public function isClientUser(): bool
    {
        return in_array($this->role, ['cliente_admin', 'cliente_colaborador'])
            || ($this->role === 'viewer' && $this->user_type === 'client');
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function isAiEmployee(): bool
    {
        return $this->user_type === 'ai' || $this->role === 'ai_employee';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canAccessAdminPanel(): bool
    {
        if (!$this->isActive()) return false;
        if ($this->isAiEmployee()) return false;
        if ($this->isClientUser()) return false;
        return $this->isAdminGeral() || $this->isAgencyUser();
    }

    public function canAccessClientPanel(): bool
    {
        if (!$this->isActive()) return false;
        if ($this->isAiEmployee()) return false;
        return $this->isClientUser() || $this->isAdminGeral();
    }

    public function belongsToCompany(?int $companyId): bool
    {
        if (!$companyId) return false;
        return $this->company_id === $companyId;
    }

    public function belongsToClient(?int $clientId): bool
    {
        if (!$clientId) return false;
        return $this->client_id === $clientId;
    }

    public function hasPermission(string $key): bool
    {
        if ($this->isAdminGeral()) {
            return true;
        }

        return $this->permissions()->where('key', $key)->exists();
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin_geral'         => 'Admin Geral',
            'agencia_admin'       => 'Admin Agência',
            'agencia_operador'    => 'Operador',
            'social_media'        => 'Social Media',
            'gestor_trafego'      => 'Gestor de Tráfego',
            'seo'                 => 'SEO',
            'copywriter'          => 'Copywriter',
            'designer'            => 'Designer',
            'cliente_admin'       => 'Admin Cliente',
            'cliente_colaborador' => 'Colaborador',
            'viewer'              => 'Visualizador',
            'ai_employee'         => 'Funcionário IA',
            default               => $this->role ?? 'Sem role',
        };
    }

    public function scopeVisibleTo($query, User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeUsers($user, $query);
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(ExternalFile::class, 'uploaded_by');
    }

    public function aiProviderCalls(): HasMany
    {
        return $this->hasMany(AiProviderCall::class);
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
}
