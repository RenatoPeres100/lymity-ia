<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'      => 'nullable|exists:clients,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'approval_type'  => 'required|in:post,campaign,budget,blog,website_page,proposal,external_action,ai_task,other',
            'sensitive_level'=> 'required|in:low,medium,high,critical',
            'payload'        => 'nullable',
            'due_at'         => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'O título é obrigatório.',
            'approval_type.required'  => 'O tipo de aprovação é obrigatório.',
            'sensitive_level.required'=> 'O nível de sensibilidade é obrigatório.',
        ];
    }
}
