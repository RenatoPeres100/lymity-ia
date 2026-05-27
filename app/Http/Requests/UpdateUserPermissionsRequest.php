<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');
        return Auth::user()?->can('managePermissions', $target) ?? false;
    }

    public function rules(): array
    {
        return [
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }
}
