<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialContentBriefRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => 'sometimes|string|max:255',
            'objective'    => 'nullable|in:awareness,engagement,leads,sales,authority,relationship',
            'content_type' => 'nullable|in:feed,reels,story,carousel,short_video,article,thread',
            'instructions' => 'nullable|string',
            'references'   => 'nullable|string',
            'tone'         => 'nullable|string|max:100',
            'due_date'     => 'nullable|date',
            'status'       => 'sometimes|in:draft,in_progress,completed',
        ];
    }
}
