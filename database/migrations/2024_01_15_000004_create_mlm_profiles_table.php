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
        Schema::create('mlm_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('referral_code')->unique();
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->string('current_rank')->default('associate');
            $table->integer('level')->default(1);
            $table->integer('total_downline')->default(0);
            $table->integer('active_downline')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('total_withdrawn', 12, 2)->default(0);
            $table->date('last_rank_update')->nullable();
            $table->json('rank_history')->nullable();
            $table->json('monthly_earnings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'referral_code']);
            $table->index(['referrer_id', 'level']);
            $table->index(['current_rank', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mlm_profiles');
    }
};
