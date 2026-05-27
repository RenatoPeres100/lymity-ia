<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->can('create', \App\Models\Client::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'legal_name'       => ['nullable', 'string', 'max:255'],
            'tax_id'           => ['nullable', 'string', 'max:50'],
            'segment'          => ['nullable', 'string', 'max:255'],
            'website'          => ['nullable', 'url', 'max:255'],
            'instagram'        => ['nullable', 'string', 'max:255'],
            'facebook'         => ['nullable', 'string', 'max:255'],
            'linkedin'         => ['nullable', 'string', 'max:255'],
            'tiktok'           => ['nullable', 'string', 'max:255'],
            'youtube'          => ['nullable', 'string', 'max:255'],
            'whatsapp'         => ['nullable', 'string', 'max:50'],
            'email'            => ['nullable', 'email', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'status'           => ['required', 'in:active,inactive,archived'],
            'brand_voice'      => ['nullable', 'string'],
            'target_audience'  => ['nullable', 'string'],
            'offer_description'=> ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status'    => $this->status ?: 'active',
            'tax_id'    => $this->tax_id ? preg_replace('/[^\d]/', '', $this->tax_id) ?: $this->tax_id : null,
            'whatsapp'  => $this->whatsapp ? preg_replace('/[\s\-\(\)]/', '', $this->whatsapp) : null,
            'instagram' => $this->instagram ? ltrim($this->instagram, '@') ? '@'.ltrim($this->instagram, '@') : null : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do cliente é obrigatório.',
            'website.url'   => 'O site deve ser uma URL válida (ex: https://site.com.br).',
            'email.email'   => 'Informe um e-mail válido.',
        ];
    }
}
