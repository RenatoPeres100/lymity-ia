<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->date('date');
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('conversions', 12, 2)->default(0);
            $table->decimal('leads', 12, 2)->default(0);
            $table->decimal('revenue', 12, 2)->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('cpc', 12, 2)->nullable();
            $table->decimal('cpa', 12, 2)->nullable();
            $table->decimal('roas', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_metrics');
    }
};
