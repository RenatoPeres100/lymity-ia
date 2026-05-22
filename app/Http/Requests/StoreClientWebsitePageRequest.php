<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientWebsitePageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'page_type'       => ['required', 'in:home,about,service,landing_page,blog_index,custom'],
            'content'         => ['nullable', 'string'],
            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'focus_keyword'   => ['nullable', 'string', 'max:255'],
            'status'          => ['required', 'in:draft,pending_approval,approved,published,archived'],
        ];
    }
}
