<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiWorkScheduleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ai_employee_id'      => 'required|exists:ai_employees,id',
            'client_id'           => 'nullable|exists:clients,id',
            'frequency'           => 'required|in:manual,hourly,daily,weekly,monthly',
            'work_minutes_per_run'=> 'nullable|integer|min:1|max:480',
            'max_tasks_per_run'   => 'nullable|integer|min:1|max:20',
            'active'              => 'nullable|boolean',
            'days_of_week'        => 'nullable|array',
            'days_of_week.*'      => 'integer|between:0,6',
            'time_of_day'         => 'nullable|date_format:H:i',
        ];
    }
}
