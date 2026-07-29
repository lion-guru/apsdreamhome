<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Models;

use App\Core\Database\Model;
use App\Models\Lead\Lead;
use App\Models\User\User;

class LeadNote extends Model
{
    protected static $table = 'lead_notes';
    protected static $tenantScoped = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'note',
        'content',
        'is_private',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Get the lead that owns the note.
     */
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the user who created the note.
     */
    public function user()
    {
        return User::find($this->created_by);
    }
}
