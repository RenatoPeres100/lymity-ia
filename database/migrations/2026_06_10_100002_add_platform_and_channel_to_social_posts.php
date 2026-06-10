<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_posts', 'platform')) {
                $table->string('platform')->nullable()->default('instagram')->after('content_type');
            }
            if (!Schema::hasColumn('social_posts', 'social_channel_id')) {
                $table->unsignedBigInteger('social_channel_id')->nullable()->after('platform');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            foreach (['platform', 'social_channel_id'] as $col) {
                if (Schema::hasColumn('social_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
