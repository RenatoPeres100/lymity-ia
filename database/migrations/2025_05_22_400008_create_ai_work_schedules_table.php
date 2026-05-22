<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_employee_id')->constrained('ai_employees')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->enum('frequency', ['manual', 'hourly', 'daily', 'weekly', 'monthly'])->default('manual');
            $table->integer('work_minutes_per_run')->default(15);
            $table->integer('max_tasks_per_run')->default(3);
            $table->boolean('active')->default(true);
            $table->json('days_of_week')->nullable();
            $table->time('time_of_day')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_work_schedules');
    }
};
