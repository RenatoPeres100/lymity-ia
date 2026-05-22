<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [
        'title', 'slug', 'client_name', 'industry',
        'challenge', 'solution', 'results', 'content',
        'testimonial', 'testimonial_author', 'tags', 'published_at',
    ];

    protected $casts = [
        'results'      => 'array',
        'tags'         => 'array',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }
}
