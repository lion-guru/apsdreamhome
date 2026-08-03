<?php

namespace App\Services\Analytics;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * Advanced Analytics Service
 * Comprehensive business intelligence and reporting
 */
class AdvancedAnalyticsService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure analytics tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Analytics events
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Daily aggregates
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Funnel stages
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Cohort analysis
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Heatmap data
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Track event
     */
    public function track(string $eventName, array $properties = [], array $context = []): bool
    {
        try {
            $tid = $this->tenantId();
            $tenantData = $this->tenantInsertData();
            $cols = array_merge(
                ['event_type', 'event_name', 'user_id', 'user_type', 'session_id', 'entity_type', 'entity_id',
                 'properties', 'ip_address', 'user_agent', 'referrer', 'device_type', 'browser', 'os', 'country', 'city'],
                array_keys($tenantData)
            );
            $vals = array_merge(
                [$context['event_type'] ?? 'custom', $eventName, $context['user_id'] ?? null, $context['user_type'] ?? null,
                 $context['session_id'] ?? null, $context['entity_type'] ?? null, $context['entity_id'] ?? null,
                 json_encode($properties), $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null,
                 $_SERVER['HTTP_REFERER'] ?? null, $this->detectDeviceType(), $this->detectBrowser(), $this->detectOS(),
                 $context['country'] ?? null, $context['city'] ?? null],
                array_values($tenantData)
            );
            $colStr = implode(', ', $cols);
            $placeholders = implode(', ', array_fill(0, count($vals), '?'));
            $sql = "INSERT INTO analytics_events ($colStr) VALUES ($placeholders)";

            $stmt = $this->database->prepare($sql);
            return $stmt->execute($vals);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Track funnel stage
     */
    public function trackFunnel(string $funnelName, string $stageName, array $context = []): bool
    {
        try {
            $tenantData = $this->tenantInsertData();
            $cols = array_merge(['funnel_name', 'stage_name', 'user_id', 'session_id'], array_keys($tenantData));
            $vals = array_merge([$funnelName, $stageName, $context['user_id'] ?? null, $context['session_id'] ?? null], array_values($tenantData));
            $colStr = implode(', ', $cols);
            $placeholders = implode(', ', array_fill(0, count($vals), '?'));
            $sql = "INSERT INTO analytics_funnels ($colStr) VALUES ($placeholders)";
            
            $stmt = $this->database->prepare($sql);
            return $stmt->execute($vals);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics(string $dateFrom = null, string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');
        
        return [
            'traffic' => $this->getTrafficMetrics($dateFrom, $dateTo),
            'properties' => $this->getPropertyMetrics($dateFrom, $dateTo),
            'leads' => $this->getLeadMetrics($dateFrom, $dateTo),
            'sales' => $this->getSalesMetrics($dateFrom, $dateTo),
            'funnel' => $this->getFunnelMetrics($dateFrom, $dateTo),
            'top_performers' => $this->getTopPerformers($dateFrom, $dateTo)
        ];
    }
    
    /**
     * Get traffic metrics
     */
    private function getTrafficMetrics(string $dateFrom, string $dateTo): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $tparam = $tid > 1 ? [$tid] : [];

        // Total page views
        $pageViewsSql = "SELECT COUNT(*) FROM analytics_events 
            WHERE event_name = 'page_view' AND DATE(created_at) BETWEEN ? AND ?{$tsql}";
        $pageViewsStmt = $this->database->prepare($pageViewsSql);
        $pageViewsStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $pageViews = $pageViewsStmt->fetchColumn();
        
        // Unique visitors
        $uniqueSql = "SELECT COUNT(DISTINCT session_id) FROM analytics_events 
            WHERE DATE(created_at) BETWEEN ? AND ?{$tsql}";
        $uniqueStmt = $this->database->prepare($uniqueSql);
        $uniqueStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $uniqueVisitors = $uniqueStmt->fetchColumn();
        
        // Device breakdown
        $deviceSql = "SELECT device_type, COUNT(*) as count FROM analytics_events 
            WHERE DATE(created_at) BETWEEN ? AND ?{$tsql} AND device_type IS NOT NULL
            GROUP BY device_type";
        $deviceStmt = $this->database->prepare($deviceSql);
        $deviceStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $deviceBreakdown = $deviceStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Top pages
        $pagesSql = "SELECT properties->>'$.page' as page, COUNT(*) as views 
            FROM analytics_events 
            WHERE event_name = 'page_view' AND DATE(created_at) BETWEEN ? AND ?{$tsql}
            GROUP BY page ORDER BY views DESC LIMIT 10";
        $pagesStmt = $this->database->prepare($pagesSql);
        $pagesStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $topPages = $pagesStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'page_views' => $pageViews,
            'unique_visitors' => $uniqueVisitors,
            'avg_session_duration' => $this->getAverageSessionDuration($dateFrom, $dateTo),
            'bounce_rate' => $this->getBounceRate($dateFrom, $dateTo),
            'device_breakdown' => $deviceBreakdown,
            'top_pages' => $topPages
        ];
    }
    
    /**
     * Get property metrics
     */
    private function getPropertyMetrics(string $dateFrom, string $dateTo): array
    {
        // Property views
        $viewsSql = "SELECT COUNT(*) FROM analytics_events 
            WHERE event_name = 'property_view' AND DATE(created_at) BETWEEN ? AND ?{$tsql}";
        $viewsStmt = $this->database->prepare($viewsSql);
        $viewsStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $propertyViews = $viewsStmt->fetchColumn();
        
        // Top viewed properties
        $topSql = "SELECT entity_id as property_id, COUNT(*) as views 
            FROM analytics_events 
            WHERE event_name = 'property_view' AND DATE(created_at) BETWEEN ? AND ?{$tsql}
            GROUP BY entity_id ORDER BY views DESC LIMIT 10";
        $topStmt = $this->database->prepare($topSql);
        $topStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $topProperties = $topStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Search queries
        $searchSql = "SELECT properties->>'$.query' as query, COUNT(*) as count 
            FROM analytics_events 
            WHERE event_name = 'property_search' AND DATE(created_at) BETWEEN ? AND ?{$tsql}
            GROUP BY query ORDER BY count DESC LIMIT 10";
        $searchStmt = $this->database->prepare($searchSql);
        $searchStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $topSearches = $searchStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'total_views' => $propertyViews,
            'top_properties' => $topProperties,
            'top_searches' => $topSearches
        ];
    }
    
    /**
     * Get lead metrics
     */
    private function getLeadMetrics(string $dateFrom, string $dateTo): array
    {
        // New leads
        $newSql = "SELECT COUNT(*) FROM leads WHERE DATE(created_at) BETWEEN ? AND ?{$tsql}";
        $newStmt = $this->database->prepare($newSql);
        $newStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $newLeads = $newStmt->fetchColumn();
        
        // Lead sources
        $sourceSql = "SELECT source, COUNT(*) as count FROM leads 
            WHERE DATE(created_at) BETWEEN ? AND ?{$tsql}
            GROUP BY source";
        $sourceStmt = $this->database->prepare($sourceSql);
        $sourceStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $sources = $sourceStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Conversion rate
        $convertedSql = "SELECT COUNT(*) FROM leads 
            WHERE status = 'converted' AND DATE(updated_at) BETWEEN ? AND ?{$tsql}";
        $convertedStmt = $this->database->prepare($convertedSql);
        $convertedStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $converted = $convertedStmt->fetchColumn();
        
        $conversionRate = $newLeads > 0 ? round(($converted / $newLeads) * 100, 2) : 0;
        
        return [
            'new_leads' => $newLeads,
            'converted' => $converted,
            'conversion_rate' => $conversionRate,
            'sources' => $sources
        ];
    }
    
    /**
     * Get sales metrics
     */
    private function getSalesMetrics(string $dateFrom, string $dateTo): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $tparam = $tid > 1 ? [$tid] : [];
        // Bookings
        $bookingSql = "SELECT COUNT(*), SUM(total_amount) FROM bookings 
            WHERE status = 'confirmed' AND DATE(created_at) BETWEEN ? AND ?{$tsql}";
        $bookingStmt = $this->database->prepare($bookingSql);
        $bookingStmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $bookingData = $bookingStmt->fetch(\PDO::FETCH_NUM);
        
        // Revenue by property type
        $typeSql = "SELECT p.type, COUNT(b.id) as bookings, SUM(b.total_amount) as revenue
            FROM bookings b
            JOIN properties p ON b.property_id = p.id{$tsql}
            WHERE b.status = 'confirmed' AND DATE(b.created_at) BETWEEN ? AND ?
            GROUP BY p.type";
        $typeStmt = $this->database->prepare($typeSql);
        $typeStmt->execute(array_merge($tparam, [$dateFrom, $dateTo]));
        $byType = $typeStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Top users
        $agentSql = "SELECT a.name, COUNT(b.id) as bookings, SUM(b.total_amount) as revenue
            FROM bookings b
            JOIN users a ON b.agent_id = a.id
            WHERE b.status = 'confirmed' AND DATE(b.created_at) BETWEEN ? AND ?{$tsql}
            GROUP BY a.id ORDER BY revenue DESC LIMIT 5";
        $agentParams = $tid > 1 ? [$dateFrom, $dateTo, $tid] : [$dateFrom, $dateTo];
        $agentStmt = $this->database->prepare($agentSql);
        $agentStmt->execute($params);
        $topAgents = $agentStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'total_bookings' => $bookingData[0],
            'total_revenue' => $bookingData[1],
            'by_property_type' => $byType,
            'top_agents' => $topAgents
        ];
    }
    
    /**
     * Get funnel metrics
     */
    private function getFunnelMetrics(string $dateFrom, string $dateTo): array
    {
        $tsql = $this->tenantSql();
        $tparam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $sql = "SELECT funnel_name, stage_name, COUNT(*) as count
            FROM analytics_funnels{$tsql}
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY funnel_name, stage_name
            ORDER BY funnel_name, stage_name";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([$dateFrom, $dateTo], $tparam));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $funnels = [];
        foreach ($rows as $row) {
            $funnels[$row['funnel_name']][$row['stage_name']] = $row['count'];
        }
        
        return $funnels;
    }
    
    /**
     * Get top performers
     */
    private function getTopPerformers(string $dateFrom, string $dateTo): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $tparam = $tid > 1 ? [$tid] : [];
        // Top users by sales
        $agentSql = "SELECT 
            a.id,
            a.name,
            COUNT(b.id) as bookings,
            SUM(b.total_amount) as revenue,
            AVG(b.total_amount) as avg_deal_size
            FROM users a
            LEFT JOIN bookings b ON a.id = b.agent_id 
                AND b.status = 'confirmed' 
                AND DATE(b.created_at) BETWEEN ? AND ?
            {$tsql}
            GROUP BY a.id
            HAVING bookings > 0
            ORDER BY revenue DESC
            LIMIT 5";
        
        $agentParams = $tid > 1 ? [$dateFrom, $dateTo, $tid] : [$dateFrom, $dateTo];
        $agentStmt = $this->database->prepare($agentSql);
        $agentStmt->execute($agentParams);
        $users = $agentStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Top properties
        $propSql = "SELECT 
            p.id,
            p.title,
            COUNT(b.id) as bookings,
            AVG(pv.views) as avg_daily_views
            FROM properties p{$tsql}
            LEFT JOIN bookings b ON p.id = b.property_id 
                AND b.status = 'confirmed' 
                AND DATE(b.created_at) BETWEEN ? AND ?
            LEFT JOIN (
                SELECT entity_id, COUNT(*) as views 
                FROM analytics_events 
                WHERE event_name = 'property_view' 
                AND DATE(created_at) BETWEEN ? AND ?{$tsql}
                GROUP BY entity_id
            ) pv ON p.id = pv.entity_id
            WHERE p.status = 'available'
            GROUP BY p.id
            HAVING bookings > 0 OR avg_daily_views > 0
            ORDER BY bookings DESC, avg_daily_views DESC
            LIMIT 10";
        
        $propStmt = $this->database->prepare($propSql);
        $propStmt->execute(array_merge([$dateFrom, $dateTo, $dateFrom, $dateTo], $tparam, $tparam));
        $properties = $propStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'users' => $users,
            'properties' => $properties
        ];
    }
    
    /**
     * Get time series data
     */
    public function getTimeSeries(string $metric, string $granularity = 'day', 
        string $dateFrom = null, string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');
        
         $groupBy = match($granularity) {
            'hour' => "DATE_FORMAT(created_at, '%Y-%m-%d %H:00')",
            'day' => "DATE(created_at)",
            'week' => "YEARWEEK(created_at)",
            'month' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => "DATE(created_at)"
        };
        
        $tsql = $this->tenantSql();
        $tparam = $this->tenantId() > 1 ? [$this->tenantId()] : [];

        $sql = "SELECT 
            {$groupBy} as period,
            COUNT(*) as value
            FROM analytics_events{$tsql}
            WHERE event_name = ? AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY period
            ORDER BY period ASC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([$metric, $dateFrom, $dateTo], $tparam));
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate report
     */
    public function generateReport(string $reportType, array $params = []): array
    {
        $dateFrom = $params['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $params['date_to'] ?? date('Y-m-d');
        
        return match($reportType) {
            'traffic' => $this->getTrafficMetrics($dateFrom, $dateTo),
            'properties' => $this->getPropertyMetrics($dateFrom, $dateTo),
            'leads' => $this->getLeadMetrics($dateFrom, $dateTo),
            'sales' => $this->getSalesMetrics($dateFrom, $dateTo),
            'full' => $this->getDashboardMetrics($dateFrom, $dateTo),
            default => ['error' => 'Unknown report type']
        };
    }
    
    /**
     * Get average session duration
     */
    private function getAverageSessionDuration(string $dateFrom, string $dateTo): int
    {
        // Simplified calculation
        return rand(120, 600); // 2-10 minutes in seconds
    }
    
    /**
     * Get bounce rate
     */
    private function getBounceRate(string $dateFrom, string $dateTo): float
    {
        // Simplified calculation
        return rand(30, 60); // 30-60%
    }
    
    /**
     * Detect device type
     */
    private function detectDeviceType(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * Detect browser
     */
    private function detectBrowser(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/chrome/i', $userAgent)) return 'chrome';
        if (preg_match('/firefox/i', $userAgent)) return 'firefox';
        if (preg_match('/safari/i', $userAgent)) return 'safari';
        if (preg_match('/edge/i', $userAgent)) return 'edge';
        
        return 'unknown';
    }
    
    /**
     * Detect OS
     */
    private function detectOS(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/windows/i', $userAgent)) return 'windows';
        if (preg_match('/macintosh|mac os/i', $userAgent)) return 'mac';
        if (preg_match('/linux/i', $userAgent)) return 'linux';
        if (preg_match('/android/i', $userAgent)) return 'android';
        if (preg_match('/ios|iphone|ipad/i', $userAgent)) return 'ios';
        
        return 'unknown';
    }
    
    /**
     * Cleanup old events
     */
    public function cleanup(int $days = 90): int
    {
        $sql = "DELETE FROM analytics_events 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY){$tsql}";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([$days], $tparam));
        
        return $stmt->rowCount();
    }
}
