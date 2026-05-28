<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->can('create', \App\Models\User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'       => ['required', 'string', 'in:admin_geral,cliente,colaborador,ai_employee'],
            'user_type'  => ['required', 'in:internal,agency,client,ai_employee'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'client_id'  => ['nullable', 'exists:clients,id'],
            'job_title'  => ['nullable', 'string', 'max:255'],
            'status'     => ['required', 'in:active,inactive,blocked'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('user_type') === 'client' && !$this->input('client_id')) {
                $v->errors()->add('client_id', 'Usuários do tipo cliente devem ter um cliente vinculado.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique'      => 'Este e-mail já está em uso.',
            'password.min'      => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed'=> 'A confirmação de senha não confere.',
            'client_id.exists'  => 'Cliente inválido.',
            'company_id.exists' => 'Empresa inválida.',
        ];
    }
}
