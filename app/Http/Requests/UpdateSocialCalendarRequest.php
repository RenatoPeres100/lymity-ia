<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialCalendarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'strategy_summary' => 'nullable|string',
            'status'           => 'sometimes|in:draft,active,archived',
        ];
    }
}
