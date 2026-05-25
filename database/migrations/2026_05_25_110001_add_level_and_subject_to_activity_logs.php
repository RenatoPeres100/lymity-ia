<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'level')) {
                $table->enum('level', ['info', 'success', 'warning', 'error', 'critical'])->default('info')->after('module');
            }
            if (!Schema::hasColumn('activity_logs', 'ai_employee_id')) {
                $table->foreignId('ai_employee_id')->nullable()->constrained('ai_employees')->nullOnDelete()->after('client_id');
            }
            if (!Schema::hasColumn('activity_logs', 'subject_type')) {
                $table->string('subject_type')->nullable()->after('ai_employee_id');
            }
            if (!Schema::hasColumn('activity_logs', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('activity_logs', 'level')        ? 'level'        : null,
                Schema::hasColumn('activity_logs', 'ai_employee_id') ? 'ai_employee_id' : null,
                Schema::hasColumn('activity_logs', 'subject_type') ? 'subject_type' : null,
                Schema::hasColumn('activity_logs', 'subject_id')   ? 'subject_id'   : null,
            ]));
        });
    }
};
