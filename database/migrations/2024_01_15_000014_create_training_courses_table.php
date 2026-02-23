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
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('overview')->nullable();
            $table->string('instructor_name')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->enum('course_type', ['basic', 'intermediate', 'advanced', 'certification'])->default('basic');
            $table->enum('category', ['sales', 'marketing', 'customer_service', 'technical', 'leadership'])->default('sales');
            $table->integer('duration_hours')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->string('thumbnail')->nullable();
            $table->json('learning_objectives')->nullable();
            $table->json('prerequisites')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('enrollment_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('rating_count')->default(0);
            $table->date('published_at')->nullable();
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'is_featured']);
            $table->index(['course_type', 'category']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_courses');
    }
};
