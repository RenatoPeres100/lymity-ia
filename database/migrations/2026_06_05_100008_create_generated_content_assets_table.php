<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_content_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('generated_content_package_id');

            $table->string('asset_type')->default('featured_image');
            $table->integer('slide_order')->nullable();
            $table->text('prompt')->nullable();
            $table->text('local_path')->nullable();
            $table->text('public_url')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('generated_content_package_id');
            $table->index('asset_type');
            $table->index('status');

            $table->foreign('generated_content_package_id')
                  ->references('id')
                  ->on('generated_content_packages')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_content_assets');
    }
};
