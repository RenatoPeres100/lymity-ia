<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_employee_id')->constrained('ai_employees')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->enum('memory_type', ['brand', 'preference', 'feedback', 'performance', 'rule', 'insight'])->default('insight');
            $table->string('title');
            $table->longText('content');
            $table->integer('weight')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_memories');
    }
};
