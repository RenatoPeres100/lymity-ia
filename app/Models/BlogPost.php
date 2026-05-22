<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'excerpt', 'content', 'featured_image',
        'category_id', 'author_id', 'client_id', 'type', 'status',
        'seo_title', 'seo_description', 'focus_keyword', 'secondary_keywords',
        'tags', 'is_featured', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags'         => 'array',
        'is_featured'  => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeAgency(Builder $query): Builder
    {
        return $query->where('type', 'agency');
    }

    public function scopeClient(Builder $query, ?int $clientId = null): Builder
    {
        $query->where('type', 'client');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        return $query;
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

    public function getReadTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return (int) ceil($words / 200);
    }
}
