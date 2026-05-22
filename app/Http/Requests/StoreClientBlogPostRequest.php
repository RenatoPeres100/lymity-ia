<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientBlogPostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'              => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:255'],
            'subtitle'           => ['nullable', 'string', 'max:255'],
            'excerpt'            => ['nullable', 'string'],
            'content'            => ['required', 'string'],
            'category_id'        => ['nullable', 'exists:blog_categories,id'],
            'status'             => ['required', 'in:draft,pending_approval,approved,published,archived'],
            'seo_title'          => ['nullable', 'string', 'max:255'],
            'seo_description'    => ['nullable', 'string'],
            'focus_keyword'      => ['nullable', 'string', 'max:255'],
            'secondary_keywords' => ['nullable', 'string'],
        ];
    }
}
