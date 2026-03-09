<?php

namespace App\Models;

use App\Models\Model;

/**
 * Custom Feature Model
 * Handles custom feature data operations
 */
class CustomFeature extends Model
{
    protected static $table = 'custom_features';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'feature_type',
        'property_id',
        'user_id',
        'feature_data',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Get features by type
     */
    public static function getByType(string $type): array
    {
        return self::where('feature_type', $type)
                   ->where('status', 'active')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get features by property
     */
    public static function getByProperty(int $propertyId): array
    {
        return self::where('property_id', $propertyId)
                   ->where('status', 'active')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get features by user
     */
    public static function getByUser(int $userId): array
    {
        return self::where('user_id', $userId)
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get virtual tours
     */
    public static function getVirtualTours(int $limit = 20): array
    {
        return self::where('feature_type', 'virtual_tour')
                   ->where('status', 'active')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get property comparisons
     */
    public static function getPropertyComparisons(int $userId, int $limit = 10): array
    {
        return self::where('feature_type', 'property_comparison')
                   ->where('user_id', $userId)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get investment calculations
     */
    public static function getInvestmentCalculations(int $userId, int $limit = 10): array
    {
        return self::where('feature_type', 'investment_calculator')
                   ->where('user_id', $userId)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get smart searches
     */
    public static function getSmartSearches(int $userId, int $limit = 10): array
    {
        return self::where('feature_type', 'smart_search')
                   ->where('user_id', $userId)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get feature statistics
     */
    public static function getStats(): array
    {
        $stats = [];

        // Total features
        $stats['total_features'] = self::count();

        // Features by type
        $typeStats = self::raw("
            SELECT feature_type, COUNT(*) as count 
            FROM " . static::$table . " 
            GROUP BY feature_type 
            ORDER BY count DESC
        ");

        $stats['by_type'] = [];
        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat['feature_type']] = $stat['count'];
        }

        // Features today
        $stats['today'] = self::raw("
            SELECT COUNT(*) as count FROM " . static::$table . " 
            WHERE DATE(created_at) = CURDATE()
        ")[0]['count'] ?? 0;

        // This week
        $stats['this_week'] = self::raw("
            SELECT COUNT(*) as count FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")[0]['count'] ?? 0;

        // Active features
        $stats['active'] = self::where('status', 'active')->count();

        return $stats;
    }

    /**
     * Create virtual tour
     */
    public static function createVirtualTour(array $data): CustomFeature
    {
        return self::create([
            'feature_type' => 'virtual_tour',
            'property_id' => $data['property_id'],
            'user_id' => $data['user_id'],
            'feature_data' => json_encode($data),
            'status' => 'active'
        ]);
    }

    /**
     * Create property comparison
     */
    public static function createPropertyComparison(array $data): CustomFeature
    {
        return self::create([
            'feature_type' => 'property_comparison',
            'user_id' => $data['user_id'],
            'feature_data' => json_encode($data),
            'status' => 'active'
        ]);
    }

    /**
     * Create investment calculation
     */
    public static function createInvestmentCalculation(array $data): CustomFeature
    {
        return self::create([
            'feature_type' => 'investment_calculator',
            'property_id' => $data['property_id'] ?? null,
            'user_id' => $data['user_id'],
            'feature_data' => json_encode($data),
            'status' => 'active'
        ]);
    }

    /**
     * Create smart search
     */
    public static function createSmartSearch(array $data): CustomFeature
    {
        return self::create([
            'feature_type' => 'smart_search',
            'user_id' => $data['user_id'],
            'feature_data' => json_encode($data),
            'status' => 'active'
        ]);
    }

    /**
     * Get feature data as array
     */
    public function getData(): array
    {
        return json_decode($this->feature_data ?? '{}', true) ?? [];
    }

    /**
     * Set feature data
     */
    public function setData(array $data): void
    {
        $this->feature_data = json_encode($data);
    }

    /**
     * Get feature type label
     */
    public function getTypeLabel(): string
    {
        $labels = [
            'virtual_tour' => 'Virtual Tour',
            'property_comparison' => 'Property Comparison',
            'neighborhood_analytics' => 'Neighborhood Analytics',
            'investment_calculator' => 'Investment Calculator',
            'smart_search' => 'Smart Search'
        ];

        return $labels[$this->feature_type] ?? ucfirst($this->feature_type);
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'archived' => 'Archived'
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
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
     * Search features
     */
    public static function search(string $term): array
    {
        return self::raw("
            SELECT * FROM " . static::$table . " 
            WHERE feature_type LIKE ? OR feature_data LIKE ?
            ORDER BY created_at DESC
            LIMIT 50
        ", ["%{$term}%", "%{$term}%"]);
    }

    /**
     * Get popular features
     */
    public static function getPopular(int $limit = 10): array
    {
        return self::raw("
            SELECT feature_type, COUNT(*) as usage_count,
                   AVG(CASE WHEN status = 'active' THEN 1 ELSE 0 END) * 100 as active_rate
            FROM " . static::$table . " 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY feature_type 
            ORDER BY usage_count DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Deactivate feature
     */
    public function deactivate(): bool
    {
        $this->status = 'inactive';
        return $this->save();
    }

    /**
     * Archive feature
     */
    public function archive(): bool
    {
        $this->status = 'archived';
        return $this->save();
    }

    /**
     * Get recent features by type
     */
    public static function getRecentByType(string $type, int $limit = 5): array
    {
        return self::where('feature_type', $type)
                   ->where('status', 'active')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get features for dashboard
     */
    public static function getDashboardData(): array
    {
        $data = [];

        // Recent virtual tours
        $data['recent_virtual_tours'] = self::getRecentByType('virtual_tour', 5);

        // Recent comparisons
        $data['recent_comparisons'] = self::getRecentByType('property_comparison', 5);

        // Recent calculations
        $data['recent_calculations'] = self::getRecentByType('investment_calculator', 5);

        // Recent searches
        $data['recent_searches'] = self::getRecentByType('smart_search', 5);

        // Statistics
        $data['stats'] = self::getStats();

        return $data;
    }
}
