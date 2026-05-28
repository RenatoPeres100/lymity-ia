<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_employees', 'provider')) {
                $table->string('provider')->nullable()->after('model_name');
            }
            if (!Schema::hasColumn('ai_employees', 'default_model')) {
                $table->string('default_model')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('ai_employees', 'daily_limit')) {
                $table->unsignedInteger('daily_limit')->nullable()->after('default_model');
            }
            if (!Schema::hasColumn('ai_employees', 'monthly_budget_usd')) {
                $table->decimal('monthly_budget_usd', 8, 2)->nullable()->after('daily_limit');
            }
            if (!Schema::hasColumn('ai_employees', 'can_generate_image')) {
                $table->boolean('can_generate_image')->default(false)->after('monthly_budget_usd');
            }
            if (!Schema::hasColumn('ai_employees', 'last_run_at')) {
                $table->timestamp('last_run_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_employees', function (Blueprint $table) {
            $cols = ['provider', 'default_model', 'daily_limit', 'monthly_budget_usd', 'can_generate_image', 'last_run_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('ai_employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
