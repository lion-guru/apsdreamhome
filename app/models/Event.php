<?php

namespace App\Models;

use App\Models\Model;

/**
 * Event Model
 * Handles event data operations
 */
class Event extends Model
{
    protected static $table = 'event_log';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'event_id',
        'event_name',
        'event_data',
        'event_type',
        'priority',
        'created_at'
    ];

    /**
     * Get events by type
     */
    public static function getByType(string $type): array
    {
        return self::where('event_type', $type)
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get events by name
     */
    public static function getByName(string $name, int $limit = 50): array
    {
        return self::where('event_name', $name)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get events by priority
     */
    public static function getByPriority(int $priority): array
    {
        return self::where('priority', $priority)
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get recent events
     */
    public static function getRecent(int $limit = 20): array
    {
        return self::orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get events from last N hours
     */
    public static function getFromLastHours(int $hours): array
    {
        return self::raw("
            SELECT * FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ORDER BY created_at DESC
        ", [$hours]);
    }

    /**
     * Get events from last N days
     */
    public static function getFromLastDays(int $days): array
    {
        return self::raw("
            SELECT * FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ", [$days]);
    }

    /**
     * Get event statistics
     */
    public static function getStats(): array
    {
        $stats = [];

        // Total events
        $stats['total'] = self::count();

        // Events today
        $stats['today'] = self::raw("
            SELECT COUNT(*) as count FROM " . static::$table . " 
            WHERE DATE(created_at) = CURDATE()
        ")[0]['count'] ?? 0;

        // Events this week
        $stats['this_week'] = self::raw("
            SELECT COUNT(*) as count FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")[0]['count'] ?? 0;

        // Events this month
        $stats['this_month'] = self::raw("
            SELECT COUNT(*) as count FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")[0]['count'] ?? 0;

        // Events by type
        $typeStats = self::raw("
            SELECT event_type, COUNT(*) as count 
            FROM " . static::$table . " 
            GROUP BY event_type 
            ORDER BY count DESC
        ");

        $stats['by_type'] = [];
        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat['event_type']] = $stat['count'];
        }

        // Events by priority
        $priorityStats = self::raw("
            SELECT priority, COUNT(*) as count 
            FROM " . static::$table . " 
            GROUP BY priority 
            ORDER BY priority DESC
        ");

        $stats['by_priority'] = [];
        foreach ($priorityStats as $stat) {
            $stats['by_priority'][$stat['priority']] = $stat['count'];
        }

        // Most frequent events
        $frequentEvents = self::raw("
            SELECT event_name, COUNT(*) as count 
            FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY event_name 
            ORDER BY count DESC 
            LIMIT 10
        ");

        $stats['frequent_events'] = $frequentEvents;

        return $stats;
    }

    /**
     * Get event data as array
     */
    public function getData(): array
    {
        return json_decode($this->event_data ?? '{}', true) ?? [];
    }

    /**
     * Set event data
     */
    public function setData(array $data): void
    {
        $this->event_data = json_encode($data);
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel(): string
    {
        $labels = [
            1 => 'Low',
            2 => 'Normal',
            3 => 'High',
            4 => 'Critical'
        ];

        return $labels[$this->priority] ?? 'Unknown';
    }

    /**
     * Get type label
     */
    public function getTypeLabel(): string
    {
        $labels = [
            'system' => 'System',
            'user' => 'User',
            'domain' => 'Domain',
            'business' => 'Business'
        ];

        return $labels[$this->event_type] ?? ucfirst($this->event_type);
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimestamp(): string
    {
        return date('Y-m-d H:i:s', strtotime($this->created_at));
    }

    /**
     * Get time ago
     */
    public function getTimeAgo(): string
    {
        $timestamp = strtotime($this->created_at);
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }

    /**
     * Search events
     */
    public static function search(string $term): array
    {
        return self::raw("
            SELECT * FROM " . static::$table . " 
            WHERE event_name LIKE ? OR event_data LIKE ?
            ORDER BY created_at DESC
            LIMIT 50
        ", ["%{$term}%", "%{$term}%"]);
    }

    /**
     * Delete old events
     */
    public static function deleteOld(int $days): int
    {
        $sql = "DELETE FROM " . static::$table . " WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return self::execute($sql, [$days]);
    }

    /**
     * Create event if not exists
     */
    public static function createIfNotExists(array $data): ?Event
    {
        $existing = self::where('event_id', $data['event_id'])->first();
        
        if ($existing) {
            return $existing;
        }

        return self::create($data);
    }
}
