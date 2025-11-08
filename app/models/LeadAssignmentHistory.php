<?php

namespace App\Models;

class LeadAssignmentHistory extends Model
{
    protected static string $table = 'lead_assignment_history';
    protected array $fillable = [
        'lead_id',
        'assigned_to',
        'assigned_by',
        'previous_assignee_id',
        'assigned_at',
        'notes',
    ];

    protected array $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the lead that was assigned.
     */
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the user to whom the lead was assigned.
     */
    public function assignee()
    {
        return User::find($this->assigned_to);
    }

    /**
     * Get the user who made the assignment.
     */
    public function assigner()
    {
        return User::find($this->assigned_by);
    }

    /**
     * Get the previous assignee.
     */
    public function previousAssignee()
    {
        return User::find($this->previous_assignee_id);
    }

    /**
     * Get the assignee's name.
     */
    public function getAssigneeNameAttribute()
    {
        $assignee = $this->assignee();
        return $assignee ? $assignee->name : 'Unassigned';
    }

    /**
     * Get the assigner's name.
     */
    public function getAssignerNameAttribute()
    {
        $assigner = $this->assigner();
        return $assigner ? $assigner->name : 'System';
    }

    /**
     * Get the previous assignee's name.
     */
    public function getPreviousAssigneeNameAttribute()
    {
        if (!$this->previous_assignee_id) {
            return 'Unassigned';
        }

        $previousAssignee = $this->previousAssignee();
        return $previousAssignee ? $previousAssignee->name : 'Unknown';
    }

    /**
     * Get assignments to a specific user.
     */
    public static function assignedTo($userId)
    {
        return static::where('assigned_to', '=', $userId);
    }

    /**
     * Get assignments made by a specific user.
     */
    public static function assignedBy($userId)
    {
        return static::where('assigned_by', '=', $userId);
    }

    /**
     * Get assignments for a specific lead.
     */
    public static function forLead($leadId)
    {
        return static::where('lead_id', '=', $leadId);
    }
}
