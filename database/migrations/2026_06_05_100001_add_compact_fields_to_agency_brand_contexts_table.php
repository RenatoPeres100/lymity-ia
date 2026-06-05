<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency_brand_contexts', function (Blueprint $table) {
            if (!Schema::hasColumn('agency_brand_contexts', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('active');
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'content_hash')) {
                $table->string('content_hash', 64)->nullable()->after('version');
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'compact_context')) {
                $table->text('compact_context')->nullable()->after('content_hash');
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'compact_context_generated_at')) {
                $table->timestamp('compact_context_generated_at')->nullable()->after('compact_context');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agency_brand_contexts', function (Blueprint $table) {
            foreach (['version', 'content_hash', 'compact_context', 'compact_context_generated_at'] as $col) {
                if (Schema::hasColumn('agency_brand_contexts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
