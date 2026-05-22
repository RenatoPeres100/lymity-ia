<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'source'  => ['nullable', 'string', 'max:255'],
            'status'  => ['required', 'in:active,draft,archived'],
        ];
    }
}
