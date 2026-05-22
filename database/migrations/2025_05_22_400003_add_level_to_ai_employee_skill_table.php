<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_employee_skill', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_employee_skill', 'level')) {
                $table->integer('level')->default(1)->after('ai_skill_id');
            }
            if (!Schema::hasIndex('ai_employee_skill', 'ai_employee_skill_ai_employee_id_ai_skill_id_unique')) {
                $table->unique(['ai_employee_id', 'ai_skill_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_employee_skill', function (Blueprint $table) {
            $table->dropColumn('level');
            $table->dropUnique(['ai_employee_id', 'ai_skill_id']);
        });
    }
};
