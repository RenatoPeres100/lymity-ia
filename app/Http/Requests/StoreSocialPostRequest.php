<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialPostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|max:255',
            'objective'         => 'required|in:awareness,engagement,leads,sales,authority,relationship',
            'content_type'      => 'required|in:feed,reels,story,carousel,short_video,article,thread',
            'main_caption'      => 'nullable|string',
            'creative_brief'    => 'nullable|string',
            'hashtags'          => 'nullable|string',
            'cta'               => 'nullable|string|max:255',
            'client_id'         => 'nullable|exists:clients,id',
            'company_id'        => 'nullable|exists:companies,id',
            'requires_approval' => 'boolean',
            'scheduled_at'      => 'nullable|date',
        ];
    }
}
