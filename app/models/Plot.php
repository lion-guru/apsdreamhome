<?php

namespace App\Models;

/**
 * Plot Model
 * Represents a land plot in the real estate system.
 * This is the canonical Plot model - maps to the `plots` table with full schema.
 */
class Plot extends Model
{
    protected static $table = 'plots';

    protected $fillable = [
        'colony_id', 'project_id', 'plot_number', 'block', 'sector',
        'plot_type', 'area_sqft', 'area_sqm', 'width_ft', 'length_ft',
        'dimension_label', 'frontage_ft', 'depth_ft', 'base_price_per_sqft',
        'price_per_sqft', 'total_price', 'negotiated_price',
        'booking_amount', 'total_paid', 'customer_id', 'booking_date',
        'sale_date', 'possession_date', 'facing', 'corner_plot',
        'park_facing', 'road_width_ft', 'lat', 'lng',
        'image', 'documents', 'status', 'payment_status',
        'is_featured', 'is_active', 'created_by', 'updated_by',
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
     * Get plots by colony
     */
    public static function byColony($colonyId, $status = null)
    {
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT * FROM plots WHERE colony_id = ?";
        $params = [$colonyId];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY plot_number";
        return $db->fetchAll($sql, $params);
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
     * Get plot by ID with colony info
     */
    public static function findWithColony($plotId)
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetch(
            "SELECT p.*, c.colony_name, c.colony_code 
             FROM plots p 
             LEFT JOIN colonies c ON p.colony_id = c.id 
             WHERE p.id = ?",
            [$plotId]
        );
    }

    /**
     * Reserve a plot
     */
    public static function reserve($plotId, $userId)
    {
        $db = \App\Core\Database::getInstance();
        return $db->query(
            "UPDATE plots SET status = 'reserved', customer_id = ?, booking_date = NOW() WHERE id = ? AND status = 'available'",
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
            "UPDATE plots SET status = 'sold', customer_id = ?, sale_date = NOW() WHERE id = ?",
            [$userId, $plotId]
        );
    }

    /**
     * Update plot price
     */
    public static function updatePrice($plotId, $pricePerSqft, $totalPrice = null)
    {
        $db = \App\Core\Database::getInstance();
        if ($totalPrice === null) {
            $plot = self::findWithColony($plotId);
            $totalPrice = $plot['area_sqft'] * $pricePerSqft;
        }
        return $db->query(
            "UPDATE plots SET price_per_sqft = ?, total_price = ?, updated_by = ?, updated_at = NOW() WHERE id = ?",
            [$pricePerSqft, $totalPrice, auth()->id() ?? 1, $plotId]
        );
    }

    /**
     * Get plot statistics for a colony
     */
    public static function getColonyStats($colonyId)
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetch(
            "SELECT 
                COUNT(*) as total_plots,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(area_sqft) as total_area,
                AVG(price_per_sqft) as avg_price_per_sqft,
                SUM(total_price) as total_value
             FROM plots WHERE colony_id = ?",
            [$colonyId]
        );
    }
}
