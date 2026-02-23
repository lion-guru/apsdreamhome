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
        Schema::create('training_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->enum('lesson_type', ['video', 'text', 'quiz', 'assignment', 'resource'])->default('text');
            $table->string('content_url')->nullable();
            $table->longText('content_text')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('resources')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('requires_completion')->default(true);
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('training_modules')->onDelete('cascade');
            $table->unique(['module_id', 'order']);
            $table->index(['module_id', 'lesson_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_lessons');
    }
};
