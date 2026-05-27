<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ResetUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');
        return Auth::user()?->can('resetPassword', $target) ?? false;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min'      => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed'=> 'A confirmação de senha não confere.',
        ];
    }
}
