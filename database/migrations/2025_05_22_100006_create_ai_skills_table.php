<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_skills', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_employee_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_skill_id')->constrained()->cascadeOnDelete();
            $table->unique(['ai_employee_id', 'ai_skill_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_employee_skill');
        Schema::dropIfExists('ai_skills');
    }
};
