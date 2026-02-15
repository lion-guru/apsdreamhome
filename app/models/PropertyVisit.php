<?php
/**
 * Property Visit Model
 */

namespace App\Models;

class PropertyVisit extends Model {
    public static $table = 'property_visits';
    
    protected array $fillable = [
        'customer_id',
        'property_id',
        'associate_id',
        'visit_date',
        'visit_type',
        'status',
        'notes',
        'feedback_rating',
        'feedback_comments',
        'interest_level',
        'follow_up_required',
        'follow_up_date',
        'created_by',
        'created_at',
        'updated_at',
        'reminder_sent',
        'feedback_requested',
        'feedback_token'
    ];

    /**
     * Get visits for reminder (scheduled for tomorrow)
     */
    public function getVisitsForReminder()
    {
        return static::query()
            ->select([
                'v.*', 'p.title as property_name', 'p.location',
                'u.uname as name', 'u.uemail as email'
            ])
            ->from(static::$table . ' as v')
            ->join('properties as p', 'v.property_id', '=', 'p.id')
            ->join('customers as c', 'v.customer_id', '=', 'c.id')
            ->join('user as u', 'c.user_id', '=', 'u.uid')
            ->where('DATE(v.visit_date)', '=', \date('Y-m-d', \strtotime('+1 day')))
            ->where('v.status', 'confirmed')
            ->where('v.reminder_sent', 0)
            ->get();
    }

    /**
     * Get visits for feedback (completed yesterday)
     */
    public function getVisitsForFeedback()
    {
        return static::query()
            ->select([
                'v.*', 'p.title as property_name',
                'u.uname as name', 'u.uemail as email'
            ])
            ->from(static::$table . ' as v')
            ->join('properties as p', 'v.property_id', '=', 'p.id')
            ->join('customers as c', 'v.customer_id', '=', 'c.id')
            ->join('user as u', 'c.user_id', '=', 'u.uid')
            ->where('DATE(v.visit_date)', '=', \date('Y-m-d', \strtotime('-1 day')))
            ->where('v.status', 'completed')
            ->where('v.feedback_requested', 0)
            ->get();
    }
    
    /**
     * Check if a time slot is available
     */
    public function isSlotAvailable($propertyId, $date, $time) {
        $count = static::query()
            ->where('property_id', $propertyId)
            ->where('visit_date', $date)
            ->where('visit_time', $time)
            ->where('status', '!=', 'cancelled')
            ->count();
        return $count == 0;
    }
}
