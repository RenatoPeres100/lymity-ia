<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'featured_image_source')) {
                $table->string('featured_image_source', 32)->nullable()
                    ->after('featured_image_generation_id')
                    ->comment('upload|external_url|ai_generated');
            }
            if (!Schema::hasColumn('blog_posts', 'featured_image_status')) {
                $table->string('featured_image_status', 32)->nullable()
                    ->after('featured_image_source')
                    ->comment('missing|valid|invalid|generated|uploaded|external|failed');
            }
            if (!Schema::hasColumn('blog_posts', 'featured_image_error')) {
                $table->text('featured_image_error')->nullable()
                    ->after('featured_image_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            foreach (['featured_image_source', 'featured_image_status', 'featured_image_error'] as $col) {
                if (Schema::hasColumn('blog_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
