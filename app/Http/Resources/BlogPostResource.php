<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'excerpt'       => $this->excerpt ?? null,
            'type'          => $this->type ?? null,
            'status'        => $this->status,
            'seo_title'     => $this->seo_title ?? null,
            'focus_keyword' => $this->focus_keyword ?? null,
            'published_at'  => isset($this->published_at) ? $this->published_at?->toIso8601String() : null,
        ];
    }
}
