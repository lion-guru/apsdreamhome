<?php

namespace App\Models;

use App\Models\Model;

class Plot extends Model {
    public static $table = 'plots';
    protected array $fillable = [
        'plot_number',
        'land_acquisition_id',
        'plot_area',
        'plot_area_unit',
        'plot_type',
        'dimensions_length',
        'dimensions_width',
        'corner_plot',
        'park_facing',
        'road_facing',
        'plot_status',
        'current_price',
        'base_price',
        'plc_amount',
        'other_charges',
        'total_price',
        'remarks',
        'created_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Get all plots with extra info
     */
    public function getAllPlots() {
        return static::query()
            ->select('p.*, u.uname as creator_name')
            ->from('plots as p')
            ->leftJoin('user as u', 'p.created_by', '=', 'u.uid')
            ->orderBy('p.created_at', 'DESC')
            ->get();
    }

    /**
     * Get investments by customer ID
     */
    public function getInvestmentsByCustomer($customerId) {
        return static::query()
            ->select('p.*, s.site_name, s.location as site_location')
            ->from('plots as p')
            ->leftJoin('site_master as s', 'p.site_id', '=', 's.id')
            ->where('p.customer_id', $customerId)
            ->where('p.status', 'active')
            ->orderBy('p.updated_at', 'DESC')
            ->get();
    }
}
