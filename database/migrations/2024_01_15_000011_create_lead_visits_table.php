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
        Schema::create('lead_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->enum('visit_type', ['site_visit', 'virtual_tour', 'phone_call', 'video_call', 'meeting'])->default('site_visit');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->date('actual_date')->nullable();
            $table->time('actual_time')->nullable();
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('outcome', ['interested', 'not_interested', 'follow_up_needed', 'proposal_made', 'deal_closed'])->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('location')->nullable();
            $table->json('participants')->nullable();
            $table->json('followup_actions')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['lead_id', 'status']);
            $table->index(['agent_id', 'scheduled_date']);
            $table->index(['visit_type', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_visits');
    }
};
