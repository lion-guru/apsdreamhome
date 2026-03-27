<?php

namespace App\Models;

/**
 * Plot Model
 * Represents a land plot in the real estate system.
 */
class Plot extends Model
{
    protected static $table = 'plots';

    protected array $fillable = [
        'project_id',
        'plot_number',
        'area_sqft',
        'area_sqyard',
        'price_per_sqft',
        'total_price',
        'status',          // available, reserved, sold, blocked
        'plot_type',       // residential, commercial, agricultural
        'facing',
        'corner_plot',
        'description',
        'latitude',
        'longitude',
        'booked_by',
        'booking_date',
        'registry_date',
    ];

    /**
     * Get all available plots
     */
    public static function available()
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetchAll("SELECT * FROM plots WHERE status = 'available' ORDER BY plot_number");
    }

    /**
     * Get plots by project
     */
    public static function byProject($projectId)
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetchAll("SELECT * FROM plots WHERE project_id = ? ORDER BY plot_number", [$projectId]);
    }

    /**
     * Reserve a plot
     */
    public static function reserve($plotId, $userId)
    {
        $db = \App\Core\Database::getInstance();
        return $db->query(
            "UPDATE plots SET status = 'reserved', booked_by = ?, booking_date = NOW() WHERE id = ? AND status = 'available'",
            [$userId, $plotId]
        );
    }

    /**
     * Mark plot as sold
     */
    public static function sell($plotId, $userId)
    {
        $db = \App\Core\Database::getInstance();
        return $db->query(
            "UPDATE plots SET status = 'sold', booked_by = ?, registry_date = NOW() WHERE id = ?",
            [$userId, $plotId]
        );
    }
}
