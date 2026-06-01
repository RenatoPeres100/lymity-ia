<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE agent_routine_runs MODIFY status VARCHAR(20) NOT NULL DEFAULT 'queued'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE agent_routine_runs MODIFY status ENUM('queued','running','completed','failed') NOT NULL");
    }
};
