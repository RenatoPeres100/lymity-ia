<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'excerpt', 'content', 'featured_image',
        'featured_image_generation_id', 'featured_image_source', 'featured_image_status', 'featured_image_error',
        'category_id', 'author_id', 'ai_employee_id', 'client_id', 'company_id', 'type', 'status',
        'seo_title', 'seo_description', 'focus_keyword', 'secondary_keywords',
        'tags', 'is_featured',
        'published_at', 'scheduled_at', 'publication_error',
        'approved_by', 'approved_at',
        'content_brief_id', 'generated_by_agent_id', 'approved_by_user_id',
        'revision_notes', 'ai_metadata',
    ];

    protected $casts = [
        'published_at'      => 'datetime',
        'scheduled_at'      => 'datetime',
        'approved_at'       => 'datetime',
        'tags'              => 'array',
        'is_featured'       => 'boolean',
        'secondary_keywords'=> 'array',
        'ai_metadata'       => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function generatedByAgent(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'generated_by_agent_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contentBrief(): BelongsTo
    {
        return $this->belongsTo(ContentBrief::class, 'content_brief_id');
    }

    public function featuredImageGeneration(): BelongsTo
    {
        return $this->belongsTo(AiImageGeneration::class, 'featured_image_generation_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeBlogPosts($user, $query);
    }

    public function scopeAgency(Builder $query): Builder
    {
        return $query->where('type', 'agency');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
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

    public function scopeDueForPublishing(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '<=', now())
                     ->whereNotNull('scheduled_at');
    }

    public function scopeClient(Builder $query, ?int $clientId = null): Builder
    {
        $query->where('type', 'client');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        return $query;
    }

    // ── Status helpers ─────────────────────────────────────────────────────────

    public function isDraft(): bool           { return $this->status === 'draft'; }
    public function isPendingApproval(): bool  { return $this->status === 'pending_approval'; }
    public function isApproved(): bool         { return $this->status === 'approved'; }
    public function isScheduled(): bool        { return $this->status === 'scheduled'; }
    public function isPublishing(): bool       { return $this->status === 'publishing'; }
    public function isPublished(): bool        { return $this->status === 'published'; }
    public function isFailed(): bool           { return $this->status === 'failed'; }
    public function isRejected(): bool         { return $this->status === 'rejected'; }
    public function isArchived(): bool         { return $this->status === 'archived'; }

    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function canBeScheduled(): bool
    {
        return in_array($this->status, ['approved', 'scheduled']);
    }

    public function canBePublished(): bool
    {
        return in_array($this->status, ['approved', 'scheduled']);
    }

    public function canSubmitForApproval(): bool
    {
        return in_array($this->status, ['draft', 'rejected', 'failed']);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando aprovação',
            'approved'         => 'Aprovado',
            'scheduled'        => 'Agendado',
            'publishing'       => 'Publicando',
            'published'        => 'Publicado',
            'failed'           => 'Falhou',
            'rejected'         => 'Reprovado',
            'archived'         => 'Arquivado',
            default            => $this->status ?? '—',
        };
    }

    public function getReadTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        return (int) ceil($words / 200);
    }
}
