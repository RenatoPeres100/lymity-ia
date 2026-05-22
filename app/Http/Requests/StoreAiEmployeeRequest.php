<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                      => 'required|string|max:100',
            'slug'                      => 'nullable|string|max:100|unique:ai_employees,slug',
            'role_key'                  => 'required|string|max:60',
            'title'                     => 'nullable|string|max:150',
            'description'               => 'nullable|string',
            'autonomy_level'            => 'nullable|in:low,medium,high',
            'status'                    => 'nullable|in:active,inactive,disabled',
            'avatar'                    => 'nullable|string|max:255',
            'default_client_id'         => 'nullable|exists:clients,id',
            'max_tasks_per_day'         => 'nullable|integer|min:1|max:100',
            'requires_approval_default' => 'nullable|boolean',
            'provider_type'             => 'nullable|string|max:50',
            'model_name'                => 'nullable|string|max:100',
            'system_prompt'             => 'nullable|string',
            'routine_description'       => 'nullable|string',
            'can_publish'               => 'nullable|boolean',
            'can_send_messages'         => 'nullable|boolean',
            'can_manage_ads_budget'     => 'nullable|boolean',
            'skills'                    => 'nullable|array',
            'skills.*'                  => 'integer|exists:ai_skills,id',
        ];
    }
}
