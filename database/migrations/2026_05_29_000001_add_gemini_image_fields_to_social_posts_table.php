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
            if (!Schema::hasColumn('social_posts', 'image_prompt')) {
                $table->text('image_prompt')->nullable()->after('public_image_url');
            }
            if (!Schema::hasColumn('social_posts', 'image_path')) {
                $table->text('image_path')->nullable()->after('image_prompt');
            }
            if (!Schema::hasColumn('social_posts', 'image_status')) {
                $table->string('image_status', 32)->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('social_posts', 'image_provider')) {
                $table->string('image_provider', 32)->nullable()->after('image_status');
            }
            if (!Schema::hasColumn('social_posts', 'image_metadata')) {
                $table->json('image_metadata')->nullable()->after('image_provider');
            }
            if (!Schema::hasColumn('social_posts', 'image_validation_status')) {
                $table->string('image_validation_status', 32)->nullable()->after('image_metadata');
            }
            if (!Schema::hasColumn('social_posts', 'image_validation_error')) {
                $table->text('image_validation_error')->nullable()->after('image_validation_status');
            }
            if (!Schema::hasColumn('social_posts', 'instagram_container_id')) {
                $table->string('instagram_container_id')->nullable()->after('image_validation_error');
            }
            if (!Schema::hasColumn('social_posts', 'permalink')) {
                $table->text('permalink')->nullable()->after('instagram_container_id');
            }
        });

        // Add indexes where safe (check they don't already exist)
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            $indexes = DB::select("SHOW INDEX FROM social_posts");
            $existingKeys = array_column($indexes, 'Key_name');

            $toAdd = [
                'social_posts_company_id_index'             => 'ADD INDEX social_posts_company_id_index (company_id)',
                'social_posts_client_id_index'              => 'ADD INDEX social_posts_client_id_index (client_id)',
                'social_posts_status_index'                 => 'ADD INDEX social_posts_status_index (status)',
                'social_posts_scheduled_at_index'           => 'ADD INDEX social_posts_scheduled_at_index (scheduled_at)',
                'social_posts_published_at_index'           => 'ADD INDEX social_posts_published_at_index (published_at)',
                'social_posts_external_post_id_index'       => 'ADD INDEX social_posts_external_post_id_index (external_post_id)',
                'social_posts_instagram_container_id_index' => 'ADD INDEX social_posts_instagram_container_id_index (instagram_container_id)',
                'social_posts_image_status_index'           => 'ADD INDEX social_posts_image_status_index (image_status)',
            ];

            $alters = [];
            foreach ($toAdd as $keyName => $clause) {
                if (!in_array($keyName, $existingKeys)) {
                    $alters[] = $clause;
                }
            }

            if (!empty($alters)) {
                DB::statement('ALTER TABLE social_posts ' . implode(', ', $alters));
            }
        }
    }

    public function down(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            $cols = [
                'image_prompt', 'image_path', 'image_status', 'image_provider',
                'image_metadata', 'image_validation_status', 'image_validation_error',
                'instagram_container_id', 'permalink',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('social_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
