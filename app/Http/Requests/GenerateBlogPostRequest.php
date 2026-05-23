<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'      => 'required|in:agency,client',
            'client_id' => 'required_if:type,client|nullable|exists:clients,id',
            'keyword'   => 'required|string|max:255',
            'title'     => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'         => 'O tipo de post é obrigatório.',
            'type.in'               => 'O tipo deve ser agência ou cliente.',
            'client_id.required_if' => 'O cliente é obrigatório para posts de cliente.',
            'keyword.required'      => 'A palavra-chave é obrigatória.',
        ];
    }
}
