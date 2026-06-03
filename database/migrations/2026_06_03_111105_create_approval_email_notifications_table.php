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
        Schema::create('approval_email_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('notification_type'); // created, reminder, status_changed
            $table->string('status')->default('pending'); // pending, sent, failed, skipped
            $table->string('subject')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('approval_request_id');
            $table->index('user_id');
            $table->index('email');
            $table->index('notification_type');
            $table->index('status');
            $table->index('sent_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_email_notifications');
    }
};
