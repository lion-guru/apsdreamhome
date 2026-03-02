<?php
/**
 * System Alert Model
 */

namespace App\Models;

class SystemAlert extends Model {
    public static $table = 'system_alerts';
    
    protected array $fillable = [
        'level',
        'title',
        'message',
        'system',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'created_at'
    ];
    
    /**
     * Get alerts for a given interval
     */
    public function getAlertsInInterval($interval) {
        return static::query()
            ->where('created_at', '>=', \date('Y-m-d H:i:s', \strtotime("-$interval")))
            ->get();
    }

    /**
     * Get alert summary for a given interval
     */
    public function getSummary($interval) {
        return static::query()
            ->select([
                "COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical",
                "COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning",
                "AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(resolved_at, NOW()))) as avg_resolution",
                "COUNT(*) / 24 as hourly_rate"
            ])
            ->where('created_at', '>=', \date('Y-m-d H:i:s', \strtotime("-$interval")))
            ->first();
    }

    /**
     * Get trends for previous period
     */
    public function getPreviousSummary($interval) {
        $start = \date('Y-m-d H:i:s', \strtotime("-2 $interval"));
        $end = \date('Y-m-d H:i:s', \strtotime("-$interval"));
        
        return static::query()
            ->select([
                "COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical",
                "COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning",
                "AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(resolved_at, NOW()))) as avg_resolution",
                "COUNT(*) / 24 as hourly_rate"
            ])
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->first();
    }

    /**
     * Get chart data
     */
    public function getChartData($groupBy, $interval) {
        return static::query()
            ->select([
                "$groupBy as label",
                "COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical",
                "COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning"
            ])
            ->where('created_at', '>=', \date('Y-m-d H:i:s', \strtotime("-$interval")))
            ->groupBy($groupBy)
            ->orderBy('label', 'ASC')
            ->get();
    }

    /**
     * Get level distribution
     */
    public function getDistribution($interval) {
        return static::query()
            ->select(['level', 'COUNT(*) as count'])
            ->where('created_at', '>=', \date('Y-m-d H:i:s', \strtotime("-$interval")))
            ->groupBy('level')
            ->get();
    }
}
