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

        // Sequential array [0 => 5, 1 => 8] means values are skill IDs (from form skills[])
        // Associative array [5 => 2, 8 => 3] means keys are skill IDs and values are levels
        $isSequential = array_keys($skills) === range(0, count($skills) - 1);

        foreach ($skills as $key => $value) {
            if (is_string($key) && !is_numeric($key)) {
                $skill = AiSkill::firstOrCreate(['name' => $key]);
                $pivot[$skill->id] = ['level' => 1];
            } elseif ($isSequential) {
                $pivot[(int) $value] = ['level' => 1];
            } else {
                $pivot[(int) $key] = ['level' => is_numeric($value) ? (int) $value : 1];
            }
        }

        $employee->skills()->sync($pivot);
    }
}
