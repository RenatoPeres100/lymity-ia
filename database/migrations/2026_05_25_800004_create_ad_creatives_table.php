<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('ad_group_id')->nullable()->constrained('ad_groups')->cascadeOnDelete();
            $table->string('title');
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->text('primary_text')->nullable();
            $table->string('cta')->nullable();
            $table->text('creative_brief')->nullable();
            $table->string('destination_url')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_creatives');
    }
};
