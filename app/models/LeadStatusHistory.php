<?php

namespace App\Models;

use App\Core\Database\Model;

class LeadStatusHistory extends Model
{
    protected static $table = 'lead_status_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'lead_id',
        'status_id',
        'changed_by',
        'notes',
        'changed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the lead that owns this status history.
     */
    public function lead()
    {
        return \App\Models\Lead\Lead::find($this->lead_id);
    }

    /**
     * Get the status for this history entry.
     */
    public function status()
    {
        return LeadStatus::find($this->status_id);
    }

    /**
     * Get the user who made the change.
     */
    public function user()
    {
        return \App\Models\User\User::find($this->changed_by);
    }
}
