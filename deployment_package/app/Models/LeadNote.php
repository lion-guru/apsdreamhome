<?php

namespace App\Models;

class LeadNote extends Model
{
    protected static $table = 'lead_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'lead_id',
        'content',
        'is_private',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
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
