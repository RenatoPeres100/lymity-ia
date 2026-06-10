<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('social_channels', 'threads_user_id')) {
                $table->string('threads_user_id')->nullable()->after('instagram_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_channels', function (Blueprint $table) {
            if (Schema::hasColumn('social_channels', 'threads_user_id')) {
                $table->dropColumn('threads_user_id');
            }
        });
    }
};
