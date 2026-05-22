<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiMemoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ai_employee_id' => 'sometimes|required|exists:ai_employees,id',
            'client_id'      => 'nullable|exists:clients,id',
            'memory_type'    => 'sometimes|required|in:brand,preference,feedback,performance,rule,insight',
            'title'          => 'sometimes|required|string|max:200',
            'content'        => 'sometimes|required|string',
            'weight'         => 'nullable|integer|min:1|max:10',
        ];
    }
}
