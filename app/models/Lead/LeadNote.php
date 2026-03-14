<?php

namespace App\Models\Lead;

use App\Core\Database\Model;

class LeadNote extends Model
{
    protected static $table = 'lead_notes';

    protected array $fillable = [
        'lead_id',
        'content',
        'is_private',
        'created_by',
    ];

    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    public function creator()
    {
        return \App\Models\User\User::find($this->created_by);
    }
}
