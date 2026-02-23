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
        Schema::create('mlm_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id');
            $table->string('referral_code');
            $table->integer('level')->default(1);
            $table->enum('status', ['pending', 'active', 'inactive'])->default('active');
            $table->date('joined_date');
            $table->decimal('commission_earned', 10, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['referrer_id', 'referred_id']);
            $table->index(['referrer_id', 'level']);
            $table->index(['status', 'level']);
            $table->index('joined_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mlm_referrals');
    }
};
