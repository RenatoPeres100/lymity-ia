<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSocialVariantsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'platforms'   => 'required|array|min:1',
            'platforms.*' => 'in:instagram,facebook,linkedin,tiktok,threads,youtube,pinterest',
        ];
    }
}
