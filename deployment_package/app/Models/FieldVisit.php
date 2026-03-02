<?php

namespace App\Models;

use App\Core\Model;

/**
 * FieldVisit Model
 * Handles field employee (associate) visits and location tracking
 */
class FieldVisit extends Model
{
    protected static $table = 'visits';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'associate_id',
        'customer_id',
        'lead_id',
        'visit_date',
        'visit_time',
        'latitude',
        'longitude',
        'location_address',
        'notes',
        'status'
    ];

    /**
     * Get the associate who made the visit
     */
    public function associate()
    {
        return $this->belongsTo(Associate::class, 'associate_id');
    }

    /**
     * Get the customer associated with the visit (if any)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the lead associated with the visit (if any)
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}
