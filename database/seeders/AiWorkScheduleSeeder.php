<?php

namespace Database\Seeders;

use App\Models\AiEmployee;
use App\Models\AiWorkSchedule;
use Illuminate\Database\Seeder;

class AiWorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            ['slug' => 'social-media-ia',     'frequency' => 'daily',  'time_of_day' => '08:00', 'work_minutes_per_run' => 30, 'max_tasks_per_run' => 3],
            ['slug' => 'seo-ia',              'frequency' => 'weekly', 'time_of_day' => '09:00', 'work_minutes_per_run' => 60, 'max_tasks_per_run' => 2],
            ['slug' => 'gestor-de-trafego-ia','frequency' => 'daily',  'time_of_day' => '07:30', 'work_minutes_per_run' => 20, 'max_tasks_per_run' => 2],
            ['slug' => 'analista-ia',         'frequency' => 'weekly', 'time_of_day' => '10:00', 'work_minutes_per_run' => 45, 'max_tasks_per_run' => 2],
            ['slug' => 'gerente-de-projeto-ia','frequency' => 'daily', 'time_of_day' => '07:00', 'work_minutes_per_run' => 15, 'max_tasks_per_run' => 5],
        ];

        foreach ($schedules as $data) {
            $employee = AiEmployee::where('slug', $data['slug'])->first();
            if (!$employee) continue;

            AiWorkSchedule::updateOrCreate(
                ['ai_employee_id' => $employee->id, 'frequency' => $data['frequency']],
                [
                    'ai_employee_id'      => $employee->id,
                    'frequency'           => $data['frequency'],
                    'time_of_day'         => $data['time_of_day'],
                    'work_minutes_per_run'=> $data['work_minutes_per_run'],
                    'max_tasks_per_run'   => $data['max_tasks_per_run'],
                    'active'              => true,
                ]
            );
        }
    }
}
