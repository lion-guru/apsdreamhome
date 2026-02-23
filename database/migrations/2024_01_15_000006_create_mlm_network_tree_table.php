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
        Schema::create('mlm_network_tree', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('depth')->default(0);
            $table->string('path')->nullable(); // Materialized path for efficient tree traversal
            $table->integer('left_bound')->nullable(); // For nested set model
            $table->integer('right_bound')->nullable(); // For nested set model
            $table->json('ancestors')->nullable(); // Array of ancestor user IDs
            $table->integer('direct_downline_count')->default(0);
            $table->integer('total_downline_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            $table->unique('user_id');
            $table->index(['parent_id', 'depth']);
            $table->index('path');
            $table->index(['left_bound', 'right_bound']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mlm_network_tree');
    }
};
