<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ai_employee_id'   => 'required|exists:ai_employees,id',
            'client_id'        => 'nullable|exists:clients,id',
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'task_type'        => 'nullable|string|max:60',
            'priority'         => 'nullable|in:low,normal,medium,high,urgent',
            'requires_approval'  => 'nullable|boolean',
            'sensitive_action'   => 'nullable|boolean',
            'metadata'           => 'nullable|array',
        ];
    }
}
