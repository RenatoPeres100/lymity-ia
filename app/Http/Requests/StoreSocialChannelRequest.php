<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialChannelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'platform'   => 'required|in:instagram,facebook,linkedin,tiktok,threads,youtube,pinterest',
            'account_name' => 'required|string|max:255',
            'client_id'  => 'nullable|exists:clients,id',
            'company_id' => 'nullable|exists:companies,id',
            'status'     => 'nullable|in:active,paused,disconnected',
        ];
    }
}
