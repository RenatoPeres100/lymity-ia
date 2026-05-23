<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('keyword');
            $table->enum('search_intent', ['informational', 'commercial', 'transactional', 'navigational'])->default('informational');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->integer('difficulty')->nullable();
            $table->integer('volume')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'used', 'archived'])->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keywords');
    }
};
