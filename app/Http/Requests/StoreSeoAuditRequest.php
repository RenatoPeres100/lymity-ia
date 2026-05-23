<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'client_id'   => 'nullable|exists:clients,id',
            'company_id'  => 'nullable|exists:companies,id',
            'website_url' => 'required|url|max:255',
            'summary'     => 'nullable|string|max:5000',
            'score'       => 'nullable|integer|min:0|max:100',
            'status'      => 'required|in:draft,completed,archived',
        ];
    }
}
