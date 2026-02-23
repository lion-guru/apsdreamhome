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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('associate_id');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('fee_amount', 5, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->string('payment_method'); // bank_transfer, paypal, etc.
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->date('payout_date');
            $table->date('processed_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('commission_ids')->nullable(); // Array of commission IDs included in this payout
            $table->timestamps();

            $table->foreign('associate_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['associate_id', 'status']);
            $table->index('payout_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
