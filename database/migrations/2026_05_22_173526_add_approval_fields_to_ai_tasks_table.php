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
        Schema::table('ai_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_tasks', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ai_tasks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('ai_tasks', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('ai_tasks', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};
