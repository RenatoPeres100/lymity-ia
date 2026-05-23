<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialCalendarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'month'            => 'required|integer|between:1,12',
            'year'             => 'required|integer|min:2024',
            'client_id'        => 'nullable|exists:clients,id',
            'company_id'       => 'nullable|exists:companies,id',
            'strategy_summary' => 'nullable|string',
            'status'           => 'nullable|in:draft,active,archived',
        ];
    }
}
