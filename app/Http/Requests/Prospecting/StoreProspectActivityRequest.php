<?php

namespace App\Http\Requests\Prospecting;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ProspectActivity::class);
    }

    public function rules(): array
    {
        return [
            'activity_type' => 'required|in:call,whatsapp,email,instagram,linkedin,meeting,note,follow_up,task,other',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:pending,done,canceled',
            'due_at'        => 'nullable|date',
            'outcome'       => 'nullable|string',
        ];
    }
}
