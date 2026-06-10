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
        'social_channel_id', 'platform',
        'title', 'objective', 'content_type', 'creative_format', 'main_caption',
        'creative_brief', 'hashtags', 'cta', 'status',
        'image_url', 'public_image_url', 'external_post_id', 'publication_error',
        'image_generation_mode',
        'image_prompt', 'image_prompt_source_hash', 'image_generated_from_caption_hash',
        'image_last_generated_at',
        'image_path', 'image_status', 'image_provider',
        'image_metadata', 'image_validation_status', 'image_validation_error',
        'ai_image_generation_id',
        'carousel_enabled', 'carousel_slide_count', 'carousel_status',
        'instagram_container_id', 'permalink',
        'scheduled_at', 'published_at', 'requires_approval',
        'approved_by', 'approved_at', 'metadata',
    ];

    protected $casts = [
        'requires_approval'          => 'boolean',
        'carousel_enabled'           => 'boolean',
        'scheduled_at'               => 'datetime',
        'published_at'               => 'datetime',
        'approved_at'                => 'datetime',
        'image_last_generated_at'    => 'datetime',
        'metadata'                   => 'array',
        'image_metadata'             => 'array',
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

    public function channel(): BelongsTo
    {
        return $this->belongsTo(SocialChannel::class, 'social_channel_id');
    }

    public function scopeThreads($query)
    {
        return $query->where('platform', 'threads');
    }

    public function scopeInstagram($query)
    {
        return $query->where('platform', 'instagram')
            ->orWhereNull('platform');
    }

    public function isThreadsPost(): bool
    {
        return $this->platform === 'threads';
    }

    public function isInstagramPost(): bool
    {
        return $this->platform === 'instagram' || $this->platform === null;
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(SocialPostVariant::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(SocialPostAsset::class)->orderBy('position');
    }

    public function validAssets(): HasMany
    {
        return $this->hasMany(SocialPostAsset::class)->where('status', 'valid')->orderBy('position');
    }

    public function aiImageGeneration(): BelongsTo
    {
        return $this->belongsTo(AiImageGeneration::class, 'ai_image_generation_id');
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function pendingApprovalRequest(): ?ApprovalRequest
    {
        return $this->approvalRequests()->where('status', 'pending')->latest()->first();
    }

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeSocialPosts($user, $query);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForAgency(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    public function scopeAgency(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeDueForPublishing(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'scheduled'])
            ->where('scheduled_at', '<=', now())
            ->whereNotNull('approved_by')
            ->whereNotNull('public_image_url')
            ->where('public_image_url', 'like', 'https://%')
            ->where('image_validation_status', 'valid');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, ['pending_approval', 'draft']);
    }

    public function canBeScheduled(): bool
    {
        return $this->status === 'approved';
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

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval', 'rejected', 'failed']);
    }

    public function canBeSubmittedForApproval(): bool
    {
        return in_array($this->status, ['draft', 'rejected'])
            && !empty(trim($this->main_caption ?? ''))
            && $this->hasValidImage();
    }

    public function hasPublicImage(): bool
    {
        return !empty($this->public_image_url) && str_starts_with($this->public_image_url, 'https://');
    }

    public function hasValidImage(): bool
    {
        return $this->hasPublicImage() && $this->image_validation_status === 'valid';
    }

    public function isImageOutdated(): bool
    {
        if (empty($this->image_generated_from_caption_hash)) {
            return false;
        }
        if (empty($this->main_caption)) {
            return false;
        }
        return $this->captionHash() !== $this->image_generated_from_caption_hash;
    }

    public function captionHash(): string
    {
        // Normalize line endings before hashing to avoid false positives from \r\n vs \n
        return md5(trim(str_replace("\r\n", "\n", $this->main_caption ?? '')));
    }

    public function hasEnoughValidAssetsForCarousel(): bool
    {
        $min = config('social.carousel.min_slides', 3);
        return $this->validAssets()->count() >= $min;
    }

    public function canBePublishedAsCarousel(): bool
    {
        return $this->carousel_enabled
            && $this->carousel_status === 'valid'
            && $this->hasEnoughValidAssetsForCarousel();
    }

    public function isAgencyPost(): bool
    {
        return is_null($this->client_id);
    }

    public function canBePublished(): bool
    {
        return in_array($this->status, ['approved', 'scheduled'])
            && !empty($this->approved_by)
            && !empty($this->approved_at)
            && $this->hasValidImage()
            && !empty(trim($this->main_caption ?? ''));
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

    public function markPublished(string $externalPostId = null, ?string $permalink = null): static
    {
        $this->update([
            'status'            => 'published',
            'published_at'      => now(),
            'external_post_id'  => $externalPostId,
            'permalink'         => $permalink ?? $this->permalink,
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
