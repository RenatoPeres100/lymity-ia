<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_routine_runs', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_routine_runs', 'run_key')) {
                $table->string('run_key', 120)->nullable()->after('agent_routine_id')->unique();
            }
            if (!Schema::hasColumn('agent_routine_runs', 'routine_type')) {
                $table->string('routine_type', 64)->nullable()->after('run_key');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('routine_type');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'items_requested')) {
                $table->unsignedSmallInteger('items_requested')->default(1)->after('error_message');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'items_created')) {
                $table->unsignedSmallInteger('items_created')->default(0)->after('items_requested');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'tasks_created')) {
                $table->unsignedSmallInteger('tasks_created')->default(0)->after('items_created');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'approvals_created')) {
                $table->unsignedSmallInteger('approvals_created')->default(0)->after('tasks_created');
            }
            if (!Schema::hasColumn('agent_routine_runs', 'metadata')) {
                $table->json('metadata')->nullable()->after('approvals_created');
            }
        });

        // Indexes
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            $existing = array_column(DB::select('SHOW INDEX FROM agent_routine_runs'), 'Key_name');
            $toAdd    = [
                'agent_routine_runs_company_id_index'  => 'ADD INDEX agent_routine_runs_company_id_index (company_id)',
                'agent_routine_runs_client_id_index'   => 'ADD INDEX agent_routine_runs_client_id_index (client_id)',
                'agent_routine_runs_scheduled_for_index'=> 'ADD INDEX agent_routine_runs_scheduled_for_index (scheduled_for)',
                'agent_routine_runs_status_index'      => 'ADD INDEX agent_routine_runs_status_index (status)',
            ];
            $alters = [];
            foreach ($toAdd as $key => $clause) {
                if (!in_array($key, $existing)) $alters[] = $clause;
            }
            if ($alters) DB::statement('ALTER TABLE agent_routine_runs ' . implode(', ', $alters));
        }
    }

    public function down(): void
    {
        Schema::table('agent_routine_runs', function (Blueprint $table) {
            foreach (['run_key','routine_type','company_id','client_id','scheduled_for',
                      'items_requested','items_created','tasks_created','approvals_created','metadata'] as $col) {
                if (Schema::hasColumn('agent_routine_runs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
