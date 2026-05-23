<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialContentBriefRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'client_id'    => 'nullable|exists:clients,id',
            'company_id'   => 'nullable|exists:companies,id',
            'objective'    => 'nullable|in:awareness,engagement,leads,sales,authority,relationship',
            'content_type' => 'nullable|in:feed,reels,story,carousel,short_video,article,thread',
            'instructions' => 'nullable|string',
            'references'   => 'nullable|string',
            'tone'         => 'nullable|string|max:100',
            'due_date'     => 'nullable|date',
        ];
    }
}
