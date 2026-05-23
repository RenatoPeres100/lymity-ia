<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoContentPlanRequest extends FormRequest
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
            'description' => 'nullable|string|max:5000',
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer|min:2024|max:2030',
            'status'      => 'required|in:draft,pending_approval,approved,active,archived',
        ];
    }
}
