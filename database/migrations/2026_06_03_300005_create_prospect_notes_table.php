<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->string('visibility')->default('internal');
            $table->timestamps();

            $table->index('prospect_lead_id');
            $table->index('company_id');
            $table->index('user_id');
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_notes');
    }
};
