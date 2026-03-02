<?php
/**
 * Traffic Stat Model
 * Handles tracking and analysis of site traffic
 */

namespace App\Models;

class TrafficStat extends Model {
    public static $table = 'traffic_stats';
    
    protected array $fillable = [
        'source',
        'medium',
        'campaign',
        'landing_page',
        'ip_address',
        'is_mobile',
        'session_id',
        'created_at'
    ];
    
    /**
     * Track a visit
     */
    public function trackVisit(array $data) {
        try {
            $stat = new self($data);
            return $stat->save();
        } catch (\Exception $e) {
            \error_log('Track visit error: ' . $e->getMessage());
            return false;
        }
    }
}
