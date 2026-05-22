<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientWebsitePage extends Model
{
    protected $fillable = [
        'client_website_id', 'title', 'slug', 'page_type', 'content',
        'seo_title', 'seo_description', 'focus_keyword',
        'status', 'created_by', 'approved_by', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(ClientWebsite::class, 'client_website_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_approval');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando aprovação',
            'approved'         => 'Aprovado',
            'published'        => 'Publicado',
            'archived'         => 'Arquivado',
            default            => $this->status ?? '—',
        };
    }

    public function getPageTypeLabelAttribute(): string
    {
        return match ($this->page_type) {
            'home'     => 'Home',
            'about'    => 'Sobre',
            'services' => 'Serviços',
            'contact'  => 'Contato',
            'blog'     => 'Blog',
            'landing'  => 'Landing Page',
            'other'    => 'Outro',
            default    => $this->page_type ?? '—',
        };
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function canBePublished(): bool
    {
        return in_array($this->status, ['approved']);
    }
}
