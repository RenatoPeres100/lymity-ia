<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoKeywordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword'       => 'required|string|max:255',
            'client_id'     => 'nullable|exists:clients,id',
            'company_id'    => 'nullable|exists:companies,id',
            'search_intent' => 'required|in:informational,commercial,transactional,navigational',
            'priority'      => 'required|in:low,medium,high',
            'difficulty'    => 'nullable|integer|min:0|max:100',
            'volume'        => 'nullable|integer|min:0',
            'status'        => 'required|in:planned,in_progress,used,archived',
            'notes'         => 'nullable|string|max:2000',
        ];
    }
}
