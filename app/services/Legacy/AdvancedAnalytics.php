<?php

namespace App\Services\Legacy;
<?php
/**
 * Advanced Analytics System - APS Dream Homes
 * Real-time tracking, user behavior analysis, and business intelligence
 */

class AdvancedAnalytics {
    private $db;
    private $trackingData = [];
    
    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        
        if ($this->db) {
            $this->initTracking();
        }
    }
    
    /**
     * Initialize tracking system
     */
    private function initTracking() {
        // Create analytics tables if not exists
        $this->createAnalyticsTables();
        
        require_once __DIR__ . '/session_helpers.php';
        ensureSessionStarted();
        
        // Track page visit
        $this->trackPageVisit();
    }
    
    /**
     * Create analytics database tables
     */
    private function createAnalyticsTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS analytics_page_views (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                user_id INT,
                page_url VARCHAR(500),
                page_title VARCHAR(200),
                referrer VARCHAR(500),
                user_agent TEXT,
                ip_address VARCHAR(45),
                visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                duration_seconds INT,
                bounce_rate BOOLEAN DEFAULT 0,
                INDEX idx_session (session_id),
                INDEX idx_user (user_id),
                INDEX idx_page (page_url),
                INDEX idx_time (visit_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS analytics_user_behavior (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                user_id INT,
                action_type VARCHAR(100),
                action_data JSON,
                element_clicked VARCHAR(200),
                scroll_depth INT,
                time_on_page INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_session (session_id),
                INDEX idx_user (user_id),
                INDEX idx_action (action_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS analytics_conversions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                user_id INT,
                conversion_type VARCHAR(100),
                conversion_value DECIMAL(10,2),
                property_id INT,
                funnel_stage VARCHAR(100),
                conversion_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                source VARCHAR(200),
                medium VARCHAR(200),
                campaign VARCHAR(200),
                INDEX idx_session (session_id),
                INDEX idx_user (user_id),
                INDEX idx_conversion (conversion_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS analytics_performance (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(100),
                metric_value DECIMAL(10,2),
                metric_unit VARCHAR(50),
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric (metric_name),
                INDEX idx_time (recorded_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($tables as $sql) {
            try {
                $this->db->execute($sql);
            } catch (\Exception $e) {
                error_log("Analytics table creation error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Track page visit
     */
    public function trackPageVisit() {
        if (!$this->db) return;

        $sessionId = session_id();
        $userId = $_SESSION['user_id'] ?? null;
        $pageUrl = $_SERVER['REQUEST_URI'] ?? 'CLI/Unknown';
        $pageTitle = $this->getPageTitle();
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
        $ipAddress = $this->getClientIP();
        
        $sql = "INSERT INTO analytics_page_views 
                (session_id, user_id, page_url, page_title, referrer, user_agent, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->execute($sql, [$sessionId, $userId, $pageUrl, $pageTitle, $referrer, $userAgent, $ipAddress]);
    }
    
    /**
     * Track user interaction
     */
    public function trackInteraction($actionType, $actionData = [], $elementClicked = '') {
        if (!$this->db) return;

        $sessionId = session_id();
        $userId = $_SESSION['user_id'] ?? null;
        
        $sql = "INSERT INTO analytics_user_behavior 
                (session_id, user_id, action_type, action_data, element_clicked) 
                VALUES (?, ?, ?, ?, ?)";
        
        $actionDataJson = json_encode($actionData);
        $this->db->execute($sql, [$sessionId, $userId, $actionType, $actionDataJson, $elementClicked]);
    }
    
    /**
     * Track conversion
     */
    public function trackConversion($conversionType, $conversionValue = 0, $propertyId = null, $funnelStage = '') {
        if (!$this->db) return;

        $sessionId = session_id();
        $userId = $_SESSION['user_id'] ?? null;
        $source = $_GET['utm_source'] ?? '';
        $medium = $_GET['utm_medium'] ?? '';
        $campaign = $_GET['utm_campaign'] ?? '';
        
        $sql = "INSERT INTO analytics_conversions 
                (session_id, user_id, conversion_type, conversion_value, property_id, funnel_stage, source, medium, campaign) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->execute($sql, [$sessionId, $userId, $conversionType, $conversionValue, $propertyId, $funnelStage, $source, $medium, $campaign]);
    }
    
    /**
     * Get real-time dashboard data
     */
    public function getDashboardData() {
        $data = [
            'visitors_today' => $this->getVisitorsToday(),
            'page_views_today' => $this->getPageViewsToday(),
            'conversions_today' => $this->getConversionsToday(),
            'bounce_rate' => $this->getBounceRate(),
            'avg_session_duration' => $this->getAvgSessionDuration(),
            'top_pages' => $this->getTopPages(),
            'conversion_funnel' => $this->getConversionFunnel(),
            'traffic_sources' => $this->getTrafficSources()
        ];
        
        return $data;
    }
    
    /**
     * Get visitors today
     */
    private function getVisitorsToday() {
        if (!$this->db) return 0;
        $sql = "SELECT COUNT(DISTINCT session_id) as count 
                FROM analytics_page_views 
                WHERE DATE(visit_time) = CURDATE()";
        $row = $this->db->fetch($sql);
        return $row['count'] ?? 0;
    }
    
    /**
     * Get page views today
     */
    private function getPageViewsToday() {
        if (!$this->db) return 0;
        $sql = "SELECT COUNT(*) as count 
                FROM analytics_page_views 
                WHERE DATE(visit_time) = CURDATE()";
        $row = $this->db->fetch($sql);
        return $row['count'] ?? 0;
    }
    
    /**
     * Get conversions today
     */
    private function getConversionsToday() {
        if (!$this->db) return 0;
        $sql = "SELECT COUNT(*) as count 
                FROM analytics_conversions 
                WHERE DATE(conversion_time) = CURDATE()";
        $row = $this->db->fetch($sql);
        return $row['count'] ?? 0;
    }
    
    /**
     * Get bounce rate
     */
    private function getBounceRate() {
        if (!$this->db) return 0;
        $sql = "SELECT AVG(bounce_rate) * 100 as rate 
                FROM analytics_page_views 
                WHERE DATE(visit_time) = CURDATE()";
        $row = $this->db->fetch($sql);
        return round($row['rate'] ?? 0, 2);
    }
    
    /**
     * Get average session duration
     */
    private function getAvgSessionDuration() {
        if (!$this->db) return 0;
        $sql = "SELECT AVG(duration_seconds) as duration 
                FROM analytics_page_views 
                WHERE DATE(visit_time) = CURDATE() 
                AND duration_seconds > 0";
        $row = $this->db->fetch($sql);
        return round($row['duration'] ?? 0, 0);
    }
    
    /**
     * Get top pages
     */
    private function getTopPages() {
        if (!$this->db) return [];
        $sql = "SELECT page_url, page_title, COUNT(*) as views 
                FROM analytics_page_views 
                WHERE DATE(visit_time) = CURDATE() 
                GROUP BY page_url, page_title 
                ORDER BY views DESC 
                LIMIT 10";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get conversion funnel
     */
    private function getConversionFunnel() {
        if (!$this->db) return [];
        $sql = "SELECT funnel_stage, COUNT(*) as conversions 
                FROM analytics_conversions 
                WHERE DATE(conversion_time) = CURDATE() 
                GROUP BY funnel_stage 
                ORDER BY conversions DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get traffic sources
     */
    private function getTrafficSources() {
        if (!$this->db) return [];
        $sql = "SELECT source, COUNT(*) as visits 
                FROM analytics_page_views 
                WHERE DATE(visit_time) = CURDATE() 
                AND source != '' 
                GROUP BY source 
                ORDER BY visits DESC 
                LIMIT 10";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get page title
     */
    private function getPageTitle() {
        // This would be implemented based on your page structure
        return "APS Dream Homes";
    }
    
    /**
     * Get client IP
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

// Initialize analytics if needed
if (!isset($analytics)) {
    $analytics = new AdvancedAnalytics();
}
?>
