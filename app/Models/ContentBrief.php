<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBrief extends Model
{
    protected $fillable = [
        'company_id', 'client_id', 'title', 'topic', 'goal', 'audience',
        'primary_keyword', 'secondary_keywords', 'funnel_stage', 'search_intent',
        'outline', 'cta_suggestion', 'status', 'scheduled_for',
        'generated_by_agent_id', 'approved_by_user_id', 'approved_at', 'metadata',
    ];

    protected $casts = [
        'secondary_keywords' => 'array',
        'outline'            => 'array',
        'metadata'           => 'array',
        'scheduled_for'      => 'datetime',
        'approved_at'        => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function generatedByAgent(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'generated_by_agent_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'content_brief_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'Rascunho',
            'generated'=> 'Gerado',
            'approved' => 'Aprovado',
            'used'     => 'Utilizado',
            'archived' => 'Arquivado',
            default    => $this->status ?? '—',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this->status, ['generated', 'approved']);
    }
}
