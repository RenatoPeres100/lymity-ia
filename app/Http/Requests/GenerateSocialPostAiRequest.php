<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSocialPostAiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|max:255',
            'objective'         => 'required|in:awareness,engagement,leads,sales,authority,relationship',
            'content_type'      => 'required|in:feed,reels,story,carousel,short_video,article,thread',
            'prompt'            => 'nullable|string|max:2000',
            'client_id'         => 'nullable|exists:clients,id',
            'company_id'        => 'nullable|exists:companies,id',
            'requires_approval' => 'boolean',
        ];
    }
}
