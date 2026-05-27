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
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'youtube')) {
                $table->string('youtube')->nullable()->after('tiktok');
            }
            if (!Schema::hasColumn('clients', 'whatsapp')) {
                $table->string('whatsapp', 50)->nullable()->after('youtube');
            }
            if (!Schema::hasColumn('clients', 'email')) {
                $table->string('email')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('clients', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $cols = array_filter(
                ['youtube', 'whatsapp', 'email', 'phone'],
                fn($c) => Schema::hasColumn('clients', $c)
            );
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
