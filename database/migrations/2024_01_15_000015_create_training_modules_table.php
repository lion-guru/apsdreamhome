<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->integer('duration_minutes')->nullable();
            $table->enum('content_type', ['video', 'document', 'quiz', 'assignment', 'discussion'])->default('video');
            $table->string('content_url')->nullable();
            $table->text('content_text')->nullable();
            $table->json('resources')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('training_courses')->onDelete('cascade');
            $table->unique(['course_id', 'order']);
            $table->index(['course_id', 'is_mandatory']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_modules');
    }
};
