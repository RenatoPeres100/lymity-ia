<?php

namespace App\Http\Requests\Prospecting;

use App\Models\ProspectStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MoveProspectStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');
        return $this->user()->can('moveStage', $lead);
    }

    public function rules(): array
    {
        return [
            'prospect_stage_id' => 'required|exists:prospect_stages,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $lead = $this->route('lead');
            $stageId = $this->input('prospect_stage_id');

            if (!$stageId) return;

            $stage = ProspectStage::find($stageId);
            if (!$stage) return;

            if ($stage->prospect_pipeline_id !== $lead->prospect_pipeline_id) {
                $v->errors()->add('prospect_stage_id', 'Esta etapa não pertence ao pipeline do lead.');
            }
        });
    }
}
