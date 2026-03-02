<?php

namespace App\Models;

class LeadActivity extends Model
{
    protected static $table = 'lead_activities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'lead_id',
        'activity_type',
        'description',
        'metadata',
        'user_id',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'metadata' => 'array',
        'created_at' => 'string',
    ];

    /**
     * Get the lead that owns the activity.
     */
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return User::find($this->user_id);
    }
}
