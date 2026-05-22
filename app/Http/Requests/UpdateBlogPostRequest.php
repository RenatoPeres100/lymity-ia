<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $postId = $this->route('blogPost')?->id ?? $this->route('blogPost');

        return [
            'title'           => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255', "unique:blog_posts,slug,{$postId}"],
            'subtitle'        => ['nullable', 'string', 'max:255'],
            'excerpt'         => ['nullable', 'string'],
            'content'         => ['required', 'string'],
            'category_id'     => ['nullable', 'exists:blog_categories,id'],
            'status'          => ['required', 'in:draft,pending_approval,approved,published,archived'],
            'seo_description' => ['nullable', 'string'],
            'tags'            => ['nullable', 'string'],
            'is_featured'     => ['nullable', 'boolean'],
            'published_at'    => ['nullable', 'date'],
        ];
    }
}
