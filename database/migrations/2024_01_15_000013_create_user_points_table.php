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
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('badge_id')->nullable();
            $table->integer('points');
            $table->enum('type', ['earned', 'spent', 'bonus', 'penalty'])->default('earned');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable(); // Model type for polymorphic relation
            $table->unsignedBigInteger('reference_id')->nullable(); // Model ID for polymorphic relation
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('badge_id')->references('id')->on('badges')->onDelete('set null');
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_points');
    }
};
