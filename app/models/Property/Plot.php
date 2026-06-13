<?php

namespace App\Models\Property;

use App\Models\Model;
use App\Core\Database\Database;

/**
 * Property Plot Model
 * Wrapper around the canonical App\Models\Plot with Property-specific queries.
 * Fixed: 'user' -> 'users', 'sites' -> 'colonies' table names.
 */
class Plot extends Model
{
    protected static $table = 'plots';

    protected $fillable = [
        'colony_id', 'plot_number', 'block', 'sector',
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
     * Get all plots with creator info
     * Fixed: 'user' -> 'users', 'uid' -> 'id'
     */
    public function getAllPlots()
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*, u.name as creator_name
             FROM plots as p
             LEFT JOIN users as u ON p.created_by = u.id
             ORDER BY p.created_at DESC"
        );
    }

    /**
     * Get investments by customer ID
     * Fixed: 'sites' -> 'colonies', 'site_id' -> 'colony_id'
     */
    public function getInvestmentsByCustomer($customerId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*, c.colony_name, c.location as colony_location
             FROM plots as p
             LEFT JOIN colonies as c ON p.colony_id = c.id
             WHERE p.customer_id = ?
             AND p.status IN ('booked', 'sold')
             ORDER BY p.updated_at DESC",
            [$customerId]
        );
    }

    /**
     * Get plots by colony with customer info
     */
    public function getPlotsByColony($colonyId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*, u.name as customer_name, u.phone as customer_phone
             FROM plots as p
             LEFT JOIN users as u ON p.customer_id = u.id
             WHERE p.colony_id = ?
             ORDER BY p.plot_number",
            [$colonyId]
        );
    }

    /**
     * Get available plots for booking
     */
    public function getAvailablePlots($colonyId = null)
    {
        $db = Database::getInstance();
        $sql = "SELECT p.*, c.colony_name, c.location as colony_location
                FROM plots as p
                LEFT JOIN colonies as c ON p.colony_id = c.id
                WHERE p.status = 'available'";
        $params = [];
        
        if ($colonyId) {
            $sql .= " AND p.colony_id = ?";
            $params[] = $colonyId;
        }
        
        $sql .= " ORDER BY p.colony_id, p.plot_number";
        return $db->fetchAll($sql, $params);
    }
}
