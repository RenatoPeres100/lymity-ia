<?php

namespace App\Http\Requests\Prospecting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProspectLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');
        return $this->user()->can('update', $lead);
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'segment'            => 'nullable|string|max:255',
            'website'            => 'nullable|url|max:255',
            'instagram'          => 'nullable|string|max:255',
            'linkedin'           => 'nullable|string|max:255',
            'whatsapp'           => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'city'               => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'source'             => 'nullable|string|max:255',
            'interest_level'     => 'nullable|in:cold,warm,hot',
            'fit_score'          => 'nullable|integer|min:0|max:100',
            'potential_value'    => 'nullable|numeric|min:0',
            'estimated_budget'   => 'nullable|string|max:100',
            'next_action'        => 'nullable|string|max:255',
            'next_follow_up_at'  => 'nullable|date',
            'last_contacted_at'  => 'nullable|date',
            'pain_points'        => 'nullable|string',
            'opportunity_summary'=> 'nullable|string',
            'notes'              => 'nullable|string',
            'lost_reason'        => 'nullable|string',
            'owner_id'           => 'nullable|exists:users,id',
        ];
    }
}
