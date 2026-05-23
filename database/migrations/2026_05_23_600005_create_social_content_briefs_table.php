<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_content_briefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->longText('target_audience')->nullable();
            $table->longText('offer')->nullable();
            $table->text('tone')->nullable();
            $table->longText('references')->nullable();
            $table->string('objective')->nullable();
            $table->string('content_type')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_content_briefs');
    }
};
