<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_routines', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_routines', 'status')) {
                $table->string('status', 20)->default('active')->after('active')
                    ->comment('active|paused|disabled');
            }
            if (!Schema::hasColumn('agent_routines', 'quantity_per_run')) {
                $table->unsignedTinyInteger('quantity_per_run')->default(1)->after('content_quantity');
            }
            if (!Schema::hasColumn('agent_routines', 'approval_lead_days')) {
                $table->unsignedTinyInteger('approval_lead_days')->default(1)->after('quantity_per_run');
            }
            if (!Schema::hasColumn('agent_routines', 'generate_ahead_days')) {
                $table->unsignedTinyInteger('generate_ahead_days')->default(1)->after('approval_lead_days');
            }
            if (!Schema::hasColumn('agent_routines', 'publication_time')) {
                $table->string('publication_time', 8)->nullable()->after('generate_ahead_days');
            }
            if (!Schema::hasColumn('agent_routines', 'target_days_of_week')) {
                $table->json('target_days_of_week')->nullable()->after('publication_time');
            }
            if (!Schema::hasColumn('agent_routines', 'target_time_of_day')) {
                $table->string('target_time_of_day', 8)->nullable()->after('target_days_of_week');
            }
            if (!Schema::hasColumn('agent_routines', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_run_at');
            }
            if (!Schema::hasColumn('agent_routines', 'timezone')) {
                $table->string('timezone', 64)->nullable()->default('America/Sao_Paulo')->after('time_of_day');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_routines', function (Blueprint $table) {
            foreach (['status','quantity_per_run','approval_lead_days','generate_ahead_days',
                      'publication_time','target_days_of_week','target_time_of_day','last_error','timezone'] as $col) {
                if (Schema::hasColumn('agent_routines', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
