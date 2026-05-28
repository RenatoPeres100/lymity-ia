<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency_brand_contexts', function (Blueprint $table) {
            if (!Schema::hasColumn('agency_brand_contexts', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('company_id');
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'seo_rules')) {
                $table->text('seo_rules')->nullable();
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'cta_default')) {
                $table->text('cta_default')->nullable();
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'active')) {
                $table->boolean('active')->default(true);
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'cached_context_key')) {
                $table->string('cached_context_key')->nullable();
            }
            if (!Schema::hasColumn('agency_brand_contexts', 'cached_at')) {
                $table->timestamp('cached_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('agency_brand_contexts', function (Blueprint $table) {
            $cols = ['client_id', 'seo_rules', 'cta_default', 'active', 'cached_context_key', 'cached_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('agency_brand_contexts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
