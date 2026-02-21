<?php

namespace App\Models;

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
        'previous_status_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'changed_at' => 'string',
    ];

    /**
     * Get the lead that owns the status history.
     */
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the status associated with the history record.
     */
    public function status()
    {
        return LeadStatus::find($this->status_id);
    }

    /**
     * Get the previous status.
     */
    public function previousStatus()
    {
        return LeadStatus::find($this->previous_status_id);
    }

    /**
     * Get the user who changed the status.
     */
    public function changedBy()
    {
        return User::find($this->changed_by);
    }

    /**
     * Get the status name with color badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        $status = $this->status();
        if (!$status) {
            return '';
        }

        return sprintf(
            '<span class="badge" style="background-color: %s; color: %s">%s</span>',
            $status->color,
            $this->getContrastColor($status->color),
            htmlspecialchars($status->name)
        );
    }

    /**
     * Get the previous status name with color badge HTML.
     */
    public function getPreviousStatusBadgeAttribute()
    {
        $previousStatus = $this->previousStatus();
        if (!$previousStatus) {
            return '<span class="badge bg-secondary">None</span>';
        }

        return sprintf(
            '<span class="badge" style="background-color: %s; color: %s">%s</span>',
            $previousStatus->color,
            $this->getContrastColor($previousStatus->color),
            htmlspecialchars($previousStatus->name)
        );
    }

    /**
     * Helper method to determine text color based on background color.
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
