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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->unique();
            $table->string('template_id')->nullable(); // WhatsApp Business API template ID
            $table->string('language_code', 5)->default('en');
            $table->enum('category', ['marketing', 'utility', 'authentication'])->default('utility');
            $table->enum('template_type', ['text', 'media', 'interactive'])->default('text');
            $table->string('header_text')->nullable();
            $table->longText('body_text');
            $table->string('footer_text')->nullable();
            $table->json('buttons')->nullable(); // For interactive templates
            $table->json('media_info')->nullable(); // For media templates
            $table->json('variables')->nullable(); // Dynamic variables in template
            $table->enum('status', ['pending', 'approved', 'rejected', 'disabled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['template_type', 'is_active']);
            $table->index('language_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
