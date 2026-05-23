<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialChannelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'account_name' => 'sometimes|string|max:255',
            'status'       => 'sometimes|in:active,paused,disconnected',
        ];
    }
}
