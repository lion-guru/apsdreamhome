<?php

namespace App\Models;

class LeadFile extends Model
{
    protected static $table = 'lead_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'lead_id',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'uploaded_by',
        'is_private',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'file_size' => 'integer',
        'is_private' => 'boolean',
    ];

    /**
     * Get the lead that owns the file.
     */
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploadedBy()
    {
        return User::find($this->uploaded_by);
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
