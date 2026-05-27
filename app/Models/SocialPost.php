<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SocialPost extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'created_by', 'ai_employee_id',
        'title', 'objective', 'content_type', 'creative_format', 'main_caption',
        'creative_brief', 'hashtags', 'cta', 'status',
        'image_url', 'public_image_url', 'external_post_id', 'publication_error',
        'scheduled_at', 'published_at', 'requires_approval',
        'approved_by', 'approved_at', 'metadata',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'scheduled_at'      => 'datetime',
        'published_at'      => 'datetime',
        'approved_at'       => 'datetime',
        'metadata'          => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(SocialPostVariant::class);
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function pendingApprovalRequest(): ?ApprovalRequest
    {
        return $this->approvalRequests()->where('status', 'pending')->latest()->first();
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForAgency(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function requiresApproval(): bool
    {
        return (bool) $this->requires_approval;
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->client?->name ?? $this->company?->name ?? 'Agência';
    }

    public function isPublishing(): bool
    {
        return $this->status === 'publishing';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasPublicImage(): bool
    {
        return !empty($this->public_image_url) && str_starts_with($this->public_image_url, 'https://');
    }

    public function isApprovedForPublishing(): bool
    {
        if (!$this->requires_approval) {
            return true;
        }
        return !empty($this->approved_by) && !empty($this->approved_at);
    }

    public function markPublishing(): static
    {
        $this->update(['status' => 'publishing']);
        return $this;
    }

    public function markPublished(string $externalPostId = null): static
    {
        $this->update([
            'status'            => 'published',
            'published_at'      => now(),
            'external_post_id'  => $externalPostId,
            'publication_error' => null,
        ]);
        return $this;
    }

    public function markFailed(string $error): static
    {
        $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $error);
        $this->update(['status' => 'failed', 'publication_error' => $safe]);
        return $this;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando aprovação',
            'approved'         => 'Aprovado',
            'scheduled'        => 'Agendado',
            'publishing'       => 'Publicando...',
            'published'        => 'Publicado',
            'failed'           => 'Falhou',
            'rejected'         => 'Rejeitado',
            'archived'         => 'Arquivado',
            default            => $this->status ?? '—',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'published'        => '#166534',
            'approved'         => '#1e3a5f',
            'scheduled'        => '#134e4a',
            'publishing'       => '#1d4ed8',
            'pending_approval' => '#78350f',
            'failed'           => '#7f1d1d',
            'rejected'         => '#7f1d1d',
            'archived'         => '#334155',
            default            => '#1e293b',
        };
    }

    public function getObjectiveLabelAttribute(): string
    {
        return match ($this->objective) {
            'awareness'    => 'Awareness',
            'engagement'   => 'Engajamento',
            'leads'        => 'Captação de Leads',
            'sales'        => 'Vendas',
            'authority'    => 'Autoridade',
            'relationship' => 'Relacionamento',
            default        => ucfirst($this->objective ?? '—'),
        };
    }

    public function getContentTypeLabelAttribute(): string
    {
        return match ($this->content_type) {
            'feed'        => 'Feed',
            'reels'       => 'Reels',
            'story'       => 'Story',
            'carousel'    => 'Carrossel',
            'short_video' => 'Vídeo Curto',
            'article'     => 'Artigo',
            'thread'      => 'Thread',
            default       => ucfirst($this->content_type ?? '—'),
        };
    }
}
