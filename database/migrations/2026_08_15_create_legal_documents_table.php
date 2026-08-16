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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index(); // e.g., 'terms-of-service', 'privacy-policy', 'associate-terms', 'agent-privacy', 'plot-booking-terms'
            $table->string('title');
            $table->string('category')->index(); // 'company', 'associate', 'agent', 'booking', 'general'
            $table->string('document_type')->index(); // 'terms', 'privacy', 'terms_conditions', 'booking_terms', 'disclaimer'
            $table->longText('content'); // HTML content
            $table->text('summary')->nullable(); // Short summary for listings
            $table->string('version')->default('1.0'); // Version tracking
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->boolean('is_mandatory')->default(false); // Must accept before proceeding
            $table->json('applies_to_roles')->nullable(); // ['customer', 'associate', 'agent', 'admin', 'employee']
            $table->json('metadata')->nullable(); // Additional metadata (effective_date, expiry_date, jurisdiction, etc.)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('effective_from')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'status']);
            $table->index(['document_type', 'status']);
            $table->index(['slug', 'status']);
        });

        // Create legal_document_versions table for version history
        Schema::create('legal_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained('legal_documents')->cascadeOnDelete();
            $table->string('version');
            $table->longText('content');
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['legal_document_id', 'version']);
        });

        // Create legal_document_acceptances table for tracking user acceptance
        Schema::create('legal_document_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained('legal_documents')->cascadeOnDelete();
            $table->morphs('user'); // polymorphic: customer, associate, agent, admin, employee
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at')->useCurrent();
            $table->string('version')->nullable();
            $table->unique(['legal_document_id', 'user_id', 'user_type'], 'unique_document_user_acceptance');
            $table->index(['user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_document_acceptances');
        Schema::dropIfExists('legal_document_versions');
        Schema::dropIfExists('legal_documents');
    }
};