<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agency roles → admin_geral
        DB::table('users')
            ->whereIn('role', [
                'agencia_admin', 'agencia_operador',
                'social_media', 'gestor_trafego', 'seo',
                'copywriter', 'designer', 'blog_writer',
            ])
            ->update(['role' => 'admin_geral', 'user_type' => 'internal']);

        // cliente_admin → cliente
        DB::table('users')
            ->where('role', 'cliente_admin')
            ->update(['role' => 'cliente', 'user_type' => 'client']);

        // cliente_colaborador → colaborador
        DB::table('users')
            ->where('role', 'cliente_colaborador')
            ->update(['role' => 'colaborador', 'user_type' => 'client']);

        // viewer (client) → colaborador
        DB::table('users')
            ->where('role', 'viewer')
            ->where('user_type', 'client')
            ->update(['role' => 'colaborador', 'user_type' => 'client']);

        // viewer (non-client) → admin_geral
        DB::table('users')
            ->where('role', 'viewer')
            ->update(['role' => 'admin_geral', 'user_type' => 'internal']);
    }

    public function down(): void
    {
        // Cannot reverse deterministically — restore from backup if needed
    }
};
