<?php

namespace App\Http\Requests\Prospecting;

use Illuminate\Foundation\Http\FormRequest;

class GenerateProspectMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('prospecting.ai.generate_message');
    }

    public function rules(): array
    {
        return [
            'channel'   => 'required|in:whatsapp,instagram,email,linkedin,call_script',
            'objective' => 'required|in:first_contact,follow_up,reengagement,meeting_invite,proposal_follow_up',
        ];
    }
}
