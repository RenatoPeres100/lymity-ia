<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role_key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'paused', 'inactive'])->default('active');
            $table->string('provider_type')->default('mock');
            $table->string('model_name')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('approval_required')->default(true);
            $table->boolean('can_publish')->default(false);
            $table->boolean('can_send_messages')->default(false);
            $table->boolean('can_manage_ads_budget')->default(false);
            $table->text('routine_description')->nullable();
            $table->longText('system_prompt')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_employees');
    }
};
