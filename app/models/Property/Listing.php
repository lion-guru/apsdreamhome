<?php
/**
 * Property Listing Model
 */

namespace App\Models;

class PropertyListing extends Model {
    public static $table = 'property_listings';
    
    protected array $fillable = [
        'owner_name',
        'email',
        'phone',
        'property_type',
        'property_title',
        'location',
        'bedrooms',
        'bathrooms',
        'area',
        'price',
        'description',
        'amenities',
        'availability',
        'images',
        'status',
        'created_at',
        'updated_at'
    ];
}
