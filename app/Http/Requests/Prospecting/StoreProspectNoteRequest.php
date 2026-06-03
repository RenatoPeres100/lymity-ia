<?php

namespace App\Http\Requests\Prospecting;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !$this->user()->isClientUser() && !$this->user()->isAiEmployee();
    }

    public function rules(): array
    {
        return [
            'note'       => 'required|string',
            'visibility' => 'nullable|in:internal,public',
        ];
    }
}
