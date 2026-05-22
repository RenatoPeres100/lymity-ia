<?php

namespace App\Services\Ai;

use App\Models\AiEmployee;
use App\Models\AiSkill;
use Illuminate\Support\Str;

class AiEmployeeService
{
    public function create(array $data): AiEmployee
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $employee = AiEmployee::create($data);

        if (!empty($data['skills'])) {
            $this->syncSkills($employee, $data['skills']);
        }

        return $employee;
    }

    public function update(AiEmployee $employee, array $data): AiEmployee
    {
        $employee->update($data);

        if (array_key_exists('skills', $data)) {
            $this->syncSkills($employee, $data['skills'] ?? []);
        }

        return $employee->fresh();
    }

    public function pause(AiEmployee $employee): AiEmployee
    {
        $employee->update(['status' => 'inactive']);
        return $employee->fresh();
    }

    public function activate(AiEmployee $employee): AiEmployee
    {
        $employee->update(['status' => 'active']);
        return $employee->fresh();
    }

    public function disable(AiEmployee $employee): AiEmployee
    {
        $employee->update(['status' => 'disabled']);
        return $employee->fresh();
    }

    private function syncSkills(AiEmployee $employee, array $skills): void
    {
        $pivot = [];
        foreach ($skills as $skillId => $level) {
            if (is_int($skillId)) {
                $pivot[$skillId] = ['level' => is_numeric($level) ? (int) $level : 1];
            } else {
                $skill = AiSkill::firstOrCreate(['name' => $skillId]);
                $pivot[$skill->id] = ['level' => 1];
            }
        }
        $employee->skills()->sync($pivot);
    }
}
