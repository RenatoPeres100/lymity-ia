<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('seo_description');
            }
            if (!Schema::hasColumn('blog_posts', 'categories')) {
                $table->json('categories')->nullable()->after('tags');
            }
            if (!Schema::hasColumn('blog_posts', 'featured_image_alt')) {
                $table->string('featured_image_alt')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('blog_posts', 'featured_image_caption')) {
                $table->string('featured_image_caption')->nullable()->after('featured_image_alt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            foreach (['meta_description', 'categories', 'featured_image_alt', 'featured_image_caption'] as $col) {
                if (Schema::hasColumn('blog_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
