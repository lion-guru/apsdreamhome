<?php

namespace App\Models;

use App\Models\Model;

/**
 * Admin Dashboard Model
 * Handles data operations for admin dashboard
 */
class AdminDashboard extends Model
{
    protected static $table = 'admin_dashboard_stats';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'stat_type',
        'stat_value',
        'stat_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get dashboard statistics for a specific date
     */
    public static function getStatsByDate(string $date): array
    {
        $stats = self::where('stat_date', $date)->get();
        
        $formattedStats = [];
        foreach ($stats as $stat) {
            $formattedStats[$stat->stat_type] = $stat->stat_value;
        }
        
        return $formattedStats;
    }

    /**
     * Save dashboard statistics
     */
    public static function saveStats(array $stats, string $date = null): bool
    {
        $date = $date ?? date('Y-m-d');
        
        try {
            foreach ($stats as $type => $value) {
                self::create([
                    'stat_type' => $type,
                    'stat_value' => $value,
                    'stat_date' => $date
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get statistics for the last N days
     */
    public static function getRecentStats(int $days = 7): array
    {
        $sql = "SELECT stat_date, stat_type, stat_value 
                FROM " . static::$table . " 
                WHERE stat_date >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                ORDER BY stat_date DESC, stat_type";
        
        $results = static::raw($sql, [$days]);
        
        $formattedStats = [];
        foreach ($results as $result) {
            $formattedStats[$result['stat_date']][$result['stat_type']] = $result['stat_value'];
        }
        
        return $formattedStats;
    }
}
