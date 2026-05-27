<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_posts', 'image_url')) {
                $table->string('image_url')->nullable()->after('metadata');
            }
            if (!Schema::hasColumn('social_posts', 'public_image_url')) {
                $table->string('public_image_url')->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('social_posts', 'external_post_id')) {
                $table->string('external_post_id')->nullable()->after('public_image_url');
            }
            if (!Schema::hasColumn('social_posts', 'publication_error')) {
                $table->text('publication_error')->nullable()->after('external_post_id');
            }
            if (!Schema::hasColumn('social_posts', 'creative_format')) {
                $table->string('creative_format')->nullable()->after('content_type');
            }
        });

        // Add 'publishing' and 'failed' to status enum (MySQL)
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE social_posts MODIFY COLUMN status ENUM(
                'draft','pending_approval','approved','scheduled','publishing','published','failed','rejected','archived'
            ) NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('social_posts', 'image_url') ? 'image_url' : null,
                Schema::hasColumn('social_posts', 'public_image_url') ? 'public_image_url' : null,
                Schema::hasColumn('social_posts', 'external_post_id') ? 'external_post_id' : null,
                Schema::hasColumn('social_posts', 'publication_error') ? 'publication_error' : null,
                Schema::hasColumn('social_posts', 'creative_format') ? 'creative_format' : null,
            ]));
        });
    }
};
