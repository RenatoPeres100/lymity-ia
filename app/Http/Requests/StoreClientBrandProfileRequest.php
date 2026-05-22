<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientBrandProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'brand_name'       => ['nullable', 'string', 'max:255'],
            'brand_positioning'=> ['nullable', 'string'],
            'tone_of_voice'    => ['nullable', 'string'],
            'target_audience'  => ['nullable', 'string'],
            'main_offer'       => ['nullable', 'string'],
            'objections'       => ['nullable', 'string'],
            'competitors'      => ['nullable', 'string'],
            'visual_style'     => ['nullable', 'string'],
            'forbidden_terms'  => ['nullable', 'string'],
            'preferred_terms'  => ['nullable', 'string'],
            'cta_examples'     => ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
