<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->string('color')->nullable();
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('prospect_pipeline_id');
            $table->index('key');
            $table->index('position');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_stages');
    }
};
