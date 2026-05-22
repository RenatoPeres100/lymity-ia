<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientAssetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', 'in:image,video,document,logo,brand_file,other'],
            'path'         => ['nullable', 'string', 'max:255'],
            'external_url' => ['nullable', 'string', 'max:500'],
            'source'       => ['required', 'in:upload,google_drive,generated,external'],
            'notes'        => ['nullable', 'string'],
        ];
    }
}
