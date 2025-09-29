<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAssignmentHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'assigned_to',
        'assigned_by',
        'previous_assignee_id',
        'assigned_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the lead that was assigned.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user to whom the lead was assigned.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who made the assignment.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the previous assignee.
     */
    public function previousAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_assignee_id');
    }

    /**
     * Get the assignee's name.
     *
     * @return string
     */
    public function getAssigneeNameAttribute()
    {
        return $this->assignee ? $this->assignee->name : 'Unassigned';
    }

    /**
     * Get the assigner's name.
     *
     * @return string
     */
    public function getAssignerNameAttribute()
    {
        return $this->assigner ? $this->assigner->name : 'System';
    }

    /**
     * Get the previous assignee's name.
     *
     * @return string
     */
    public function getPreviousAssigneeNameAttribute()
    {
        if (!$this->previous_assignee_id) {
            return 'Unassigned';
        }
        
        return $this->previousAssignee ? $this->previousAssignee->name : 'Unknown';
    }

    /**
     * Scope a query to only include assignments to a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include assignments made by a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedBy($query, $userId)
    {
        return $query->where('assigned_by', $userId);
    }

    /**
     * Scope a query to only include assignments for a specific lead.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $leadId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLead($query, $leadId)
    {
        return $query->where('lead_id', $leadId);
    }
}
