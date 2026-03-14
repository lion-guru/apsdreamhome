<?php

namespace App\Models\Lead;

use App\Core\Database\Model;
use App\Models\User\User;

class LeadAssignmentHistory extends Model
{
    protected static $table = 'lead_assignment_history';

    protected array $fillable = [
        'lead_id',
        'assigned_from',
        'assigned_to',
        'assigned_by',
        'notes',
    ];

    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    public function assignedFrom()
    {
        return User::find($this->assigned_from);
    }

    public function assignedTo()
    {
        return User::find($this->assigned_to);
    }

    public function assignedBy()
    {
        return User::find($this->assigned_by);
    }
}
