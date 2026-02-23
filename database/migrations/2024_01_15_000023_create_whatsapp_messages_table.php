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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable();
            $table->string('wa_id')->nullable(); // WhatsApp ID
            $table->string('phone_number');
            $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
            $table->enum('message_type', ['text', 'image', 'document', 'audio', 'video', 'location', 'contact', 'sticker'])->default('text');
            $table->longText('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_caption')->nullable();
            $table->json('media_metadata')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->string('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['phone_number', 'direction']);
            $table->index(['message_type', 'status']);
            $table->index('sent_at');
            $table->index('wa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
