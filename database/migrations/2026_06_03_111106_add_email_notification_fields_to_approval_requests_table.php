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
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('rejected_at');
            $table->string('notification_status')->nullable()->default('not_sent')->after('notified_at');
            $table->unsignedTinyInteger('reminder_count')->default(0)->after('notification_status');
            $table->timestamp('last_reminder_at')->nullable()->after('reminder_count');
        });
    }

    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn(['notified_at', 'notification_status', 'reminder_count', 'last_reminder_at']);
        });
    }
};
