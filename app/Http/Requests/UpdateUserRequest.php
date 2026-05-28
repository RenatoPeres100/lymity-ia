<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');
        return Auth::user()?->can('update', $target) ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'role'       => ['required', 'string', 'in:admin_geral,agencia_admin,agencia_operador,social_media,copywriter,blog_writer,seo,designer,gestor_trafego,cliente_admin,cliente_colaborador,viewer,ai_employee'],
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
            $actor  = Auth::user();
            $target = $this->route('user');
            if ($target && $target->role === 'admin_geral' && !$actor?->isAdminGeral()) {
                $v->errors()->add('role', 'Você não pode editar um Admin Geral.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique'     => 'Este e-mail já está em uso.',
            'client_id.exists' => 'Cliente inválido.',
        ];
    }
}
