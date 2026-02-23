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
        Schema::create('property_comparisons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('comparison_name')->nullable();
            $table->json('property_ids'); // Array of property IDs to compare
            $table->json('comparison_criteria')->nullable(); // What aspects to compare
            $table->json('comparison_data')->nullable(); // Cached comparison results
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
            $table->index('last_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_comparisons');
    }
};
