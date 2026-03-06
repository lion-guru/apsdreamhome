<?php
/**
 * Property Viewing Model
 */

namespace App\Models;

class PropertyViewing extends Model {
    public static $table = 'property_viewings';
    
    protected array $fillable = [
        'name',
        'email',
        'phone',
        'property_id',
        'property_title',
        'preferred_date',
        'preferred_time',
        'alternate_date',
        'special_requests',
        'buyer_type',
        'budget_range',
        'financing_needed',
        'status',
        'created_at',
        'updated_at'
    ];
}
