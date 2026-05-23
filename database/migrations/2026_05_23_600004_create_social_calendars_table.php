<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->longText('strategy_summary')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'active', 'archived'])->default('draft');
            $table->timestamps();
            $table->index(['client_id', 'company_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_calendars');
    }
};
