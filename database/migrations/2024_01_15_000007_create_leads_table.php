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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('lead_source'); // website, referral, social_media, etc.
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->enum('lead_status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost', 'cancelled'])->default('new');
            $table->enum('lead_priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->text('requirements')->nullable();
            $table->text('notes')->nullable();
            $table->date('last_contact_date')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['lead_status', 'lead_priority']);
            $table->index(['agent_id', 'lead_status']);
            $table->index('email');
            $table->index('next_followup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
