<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agent_routine_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_routine_id')->constrained('agent_routines')->cascadeOnDelete();
            $table->foreignId('ai_employee_id')->constrained('ai_employees')->cascadeOnDelete();
            $table->enum('status', ['queued', 'running', 'completed', 'failed']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('output_summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_routine_runs');
    }
};
