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
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->decimal('demographic_score', 5, 2)->default(0);
            $table->decimal('engagement_score', 5, 2)->default(0);
            $table->decimal('behavior_score', 5, 2)->default(0);
            $table->decimal('source_score', 5, 2)->default(0);
            $table->decimal('total_score', 6, 2)->default(0);
            $table->string('grade')->nullable(); // A, B, C, D, F
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->json('scoring_details')->nullable();
            $table->date('last_scored_at');
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->index(['lead_id', 'total_score']);
            $table->index(['grade', 'priority']);
            $table->index('last_scored_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scores');
    }
};
