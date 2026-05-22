<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_task_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_task_logs', 'ai_employee_id')) {
                $table->foreignId('ai_employee_id')->nullable()->constrained('ai_employees')->nullOnDelete()->after('ai_task_id');
            }
            if (!Schema::hasColumn('ai_task_logs', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()->after('ai_employee_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_task_logs', function (Blueprint $table) {
            $table->dropForeign(['ai_employee_id']);
            $table->dropForeign(['client_id']);
            $table->dropColumn(['ai_employee_id', 'client_id']);
        });
    }
};
