<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'status_id',
        'changed_by',
        'previous_status_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the lead that owns the status history.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the status associated with the history record.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    /**
     * Get the previous status.
     */
    public function previousStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'previous_status_id');
    }

    /**
     * Get the user who changed the status.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the status name with color badge HTML.
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        if (!$this->status) {
            return '';
        }

        return sprintf(
            '<span class="badge" style="background-color: %s; color: %s">%s</span>',
            $this->status->color,
            $this->getContrastColor($this->status->color),
            e($this->status->name)
        );
    }

    /**
     * Get the previous status name with color badge HTML.
     *
     * @return string
     */
    public function getPreviousStatusBadgeAttribute()
    {
        if (!$this->previousStatus) {
            return '<span class="badge bg-secondary">None</span>';
        }

        return sprintf(
            '<span class="badge" style="background-color: %s; color: %s">%s</span>',
            $this->previousStatus->color,
            $this->getContrastColor($this->previousStatus->color),
            e($this->previousStatus->name)
        );
    }

    /**
     * Helper method to determine text color based on background color.
     *
     * @param  string  $hexColor
     * @return string
     */
    protected function getContrastColor($hexColor)
    {
        // Remove # if present
        $hexColor = ltrim($hexColor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Calculate luminance (perceived brightness)
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        // Return black for light colors, white for dark colors
        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
}
