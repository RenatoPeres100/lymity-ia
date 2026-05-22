<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientWebsiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'domain'          => ['nullable', 'string', 'max:255'],
            'platform'        => ['required', 'in:internal,wordpress,external'],
            'status'          => ['required', 'in:planning,active,paused,archived'],
            'primary_color'   => ['nullable', 'string', 'max:50'],
            'secondary_color' => ['nullable', 'string', 'max:50'],
            'logo'            => ['nullable', 'string', 'max:255'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}
