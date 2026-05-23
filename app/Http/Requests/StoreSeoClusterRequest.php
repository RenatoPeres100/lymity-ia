<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'client_id'    => 'nullable|exists:clients,id',
            'company_id'   => 'nullable|exists:companies,id',
            'description'  => 'nullable|string|max:2000',
            'main_keyword' => 'required|string|max:255',
            'status'       => 'required|in:active,paused,archived',
        ];
    }
}
