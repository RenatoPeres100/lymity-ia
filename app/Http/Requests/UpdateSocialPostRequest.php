<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialPostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'            => 'sometimes|string|max:255',
            'objective'        => 'sometimes|in:awareness,engagement,leads,sales,authority,relationship',
            'content_type'     => 'sometimes|in:feed,reels,story,carousel,short_video,article,thread',
            'creative_format'  => 'nullable|string|max:100',
            'main_caption'     => 'nullable|string',
            'creative_brief'   => 'nullable|string',
            'hashtags'         => 'nullable|string',
            'cta'              => 'nullable|string|max:500',
            'image_prompt'     => 'nullable|string',
            'public_image_url' => 'nullable|url',
            'scheduled_at'     => 'nullable|date',
        ];
    }
}
