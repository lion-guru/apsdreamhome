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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('property_type'); // apartment, house, villa, commercial, land
            $table->string('listing_type'); // sale, rent
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('INR');
            $table->decimal('area_sqft', 10, 2)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('parking_spaces')->nullable();
            $table->string('furnishing_status')->nullable(); // furnished, semi-furnished, unfurnished
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('India');
            $table->string('zip_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['draft', 'active', 'sold', 'rented', 'inactive'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('featured_until')->nullable();
            $table->json('seo_meta')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['property_type', 'listing_type']);
            $table->index(['status', 'featured']);
            $table->index(['city', 'state']);
            $table->index('price');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
