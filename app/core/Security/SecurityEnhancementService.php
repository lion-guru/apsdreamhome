<?php

namespace App\Core\Security;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Security Enhancement Service - APS Dream Home
 * Comprehensive security monitoring, vulnerability detection, and protection
 * Custom MVC implementation without Laravel dependencies
 */
class SecurityEnhancementService
{
    private static $instance = null;
    private $database;
    private $logger;
    private $securityConfig;
    private $blockedIPs = [];
    private $rateLimits = [];

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = LoggingService::getInstance();
        $this->securityConfig = $this->loadSecurityConfig();
        $this->createSecurityTables();
        $this->loadSecurityData();
    }

    /**
     * Create security monitoring tables
     */
    private function createSecurityTables()
    {
        try {
            // Security events log
            $sql = "CREATE TABLE IF NOT EXISTS security_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                request_uri TEXT,
                request_method VARCHAR(10),
                user_id BIGINT(20) UNSIGNED NULL,
                details JSON,
                blocked BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_severity (severity),
                INDEX idx_ip_address (ip_address),
                INDEX idx_created_at (created_at),
                INDEX idx_blocked (blocked)
            )";
            $this->database->execute($sql);

            // Blocked IPs table
            $sql = "CREATE TABLE IF NOT EXISTS blocked_ips (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL UNIQUE,
                reason VARCHAR(255),
                blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                permanent BOOLEAN DEFAULT FALSE,
                blocked_by VARCHAR(50),
                INDEX idx_ip_address (ip_address),
                INDEX idx_expires_at (expires_at)
            )";
            $this->database->execute($sql);

            // Rate limiting table
            $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                request_type VARCHAR(50) NOT NULL,
                request_count INT DEFAULT 1,
                window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                window_duration INT DEFAULT 300, -- 5 minutes
                blocked BOOLEAN DEFAULT FALSE,
                INDEX idx_ip_type (ip_address, request_type),
                INDEX idx_window_start (window_start)
            )";
            $this->database->execute($sql);

            // Vulnerability scans
            $sql = "CREATE TABLE IF NOT EXISTS vulnerability_scans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                scan_type VARCHAR(50) NOT NULL,
                target VARCHAR(255),
                vulnerability_type VARCHAR(100),
                severity ENUM('low', 'medium', 'high', 'critical'),
                description TEXT,
                recommendation TEXT,
                status ENUM('open', 'fixed', 'ignored') DEFAULT 'open',
                discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fixed_at TIMESTAMP NULL,
                INDEX idx_scan_type (scan_type),
                INDEX idx_severity (severity),
                INDEX idx_status (status)
            )";
            $this->database->execute($sql);
        } catch (Exception $e) {
            $this->logger->log("Error creating security tables: " . $e->getMessage(), 'error', 'security');
        }
    }

    /**
     * Monitor incoming request for security threats
     */
    public function monitorRequest($request)
    {
        $ipAddress = $this->getClientIP($request);
        $userAgent = $request['user_agent'] ?? '';
        $requestUri = $request['request_uri'] ?? '';
        $requestMethod = $request['request_method'] ?? 'GET';

        // Check if IP is blocked
        if ($this->isIPBlocked($ipAddress)) {
            $this->logSecurityEvent('blocked_ip_request', 'high', $ipAddress, $request, 'IP address is blocked');
            return false;
        }

        // Check rate limiting
        if (!$this->checkRateLimit($ipAddress, $requestMethod)) {
            $this->logSecurityEvent('rate_limit_exceeded', 'medium', $ipAddress, $request, 'Rate limit exceeded');
            return false;
        }

        // Check for common attack patterns
        $threats = $this->detectThreats($request);

        foreach ($threats as $threat) {
            $this->handleThreat($threat, $ipAddress, $request);

            // Auto-block for critical threats
            if ($threat['severity'] === 'critical') {
                $this->blockIP($ipAddress, 'Critical security threat detected', 24);
                return false;
            }
        }

        return true;
    }

    /**
     * Detect security threats in request
     */
    public function detectThreats($request)
    {
        $threats = [];
        $requestUri = $request['request_uri'] ?? '';
        $userAgent = $request['user_agent'] ?? '';
        $postParams = $request['post_params'] ?? [];

        // SQL Injection detection
        $sqlPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(\'|\"|`|;|--|\/\*|\*\/)/',
            '/(\b(OR|AND)\b\s+\d+\s*=\s*\d+)/i',
            '/(\b(OR|AND)\b\s+\'\w+\'\s*=\s*\'\w+\')/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $requestUri) || preg_match($pattern, json_encode($postParams))) {
                $threats[] = [
                    'type' => 'sql_injection',
                    'severity' => 'critical',
                    'description' => 'SQL injection pattern detected',
                    'pattern' => $pattern
                ];
            }
        }

        // XSS detection
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/i',
            '/<iframe[^>]*>.*?<\/iframe>/i',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $requestUri) || preg_match($pattern, json_encode($postParams))) {
                $threats[] = [
                    'type' => 'xss',
                    'severity' => 'high',
                    'description' => 'Cross-site scripting pattern detected',
                    'pattern' => $pattern
                ];
            }
        }

        // Path traversal detection
        $pathTraversalPatterns = [
            '/\.\.[\/\\\\]/',
            '/%2e%2e[\/\\\\]/i',
            '/\.\.%2f/i',
            '/%2e%2e%2f/i'
        ];

        foreach ($pathTraversalPatterns as $pattern) {
            if (preg_match($pattern, $requestUri)) {
                $threats[] = [
                    'type' => 'path_traversal',
                    'severity' => 'high',
                    'description' => 'Path traversal attempt detected',
                    'pattern' => $pattern
                ];
            }
        }

        // Command injection detection
        $commandPatterns = [
            '/;\s*(rm|del|format|shutdown|reboot)/i',
            '/\|\s*(nc|netcat|wget|curl)/i',
            '/&&\s*(rm|del|format)/i'
        ];

        foreach ($commandPatterns as $pattern) {
            if (preg_match($pattern, json_encode($postParams))) {
                $threats[] = [
                    'type' => 'command_injection',
                    'severity' => 'critical',
                    'description' => 'Command injection attempt detected',
                    'pattern' => $pattern
                ];
            }
        }

        // Suspicious user agents
        $suspiciousAgents = [
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/masscan/i',
            '/python-requests/i',
            '/curl/i'
        ];

        foreach ($suspiciousAgents as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $threats[] = [
                    'type' => 'suspicious_user_agent',
                    'severity' => 'medium',
                    'description' => 'Suspicious user agent detected',
                    'pattern' => $pattern
                ];
            }
        }

        return $threats;
    }

    /**
     * Handle detected threat
     */
    private function handleThreat($threat, $ipAddress, $request)
    {
        $this->logSecurityEvent($threat['type'], $threat['severity'], $ipAddress, $request, $threat['description']);

        // Increment threat count for IP
        $this->incrementThreatCount($ipAddress, $threat['type']);

        // Take automated action based on severity
        switch ($threat['severity']) {
            case 'critical':
                $this->blockIP($ipAddress, 'Critical security threat: ' . $threat['type'], 24);
                break;
            case 'high':
                // Block after 3 high-severity threats
                if ($this->getThreatCount($ipAddress, 'high') >= 3) {
                    $this->blockIP($ipAddress, 'Multiple high-severity threats', 12);
                }
                break;
            case 'medium':
                // Block after 10 medium-severity threats
                if ($this->getThreatCount($ipAddress, 'medium') >= 10) {
                    $this->blockIP($ipAddress, 'Multiple medium-severity threats', 6);
                }
                break;
        }
    }

    /**
     * Block IP address
     */
    public function blockIP($ipAddress, $reason, $hours = 24)
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));

            $sql = "INSERT INTO blocked_ips (ip_address, reason, expires_at, blocked_by) 
                    VALUES (?, ?, ?, 'SecurityEnhancementService')
                    ON DUPLICATE KEY UPDATE 
                    reason = VALUES(reason), 
                    expires_at = VALUES(expires_at), 
                    blocked_at = NOW()";

            $this->database->execute($sql, [$ipAddress, $reason, $expiresAt]);

            $this->blockedIPs[$ipAddress] = true;

            $this->logger->log("IP blocked: $ipAddress - Reason: $reason", 'warning', 'security');
        } catch (Exception $e) {
            $this->logger->log("Error blocking IP: " . $e->getMessage(), 'error', 'security');
        }
    }

    /**
     * Check if IP is blocked
     */
    public function isIPBlocked($ipAddress)
    {
        if (isset($this->blockedIPs[$ipAddress])) {
            return true;
        }

        try {
            $sql = "SELECT COUNT(*) as count FROM blocked_ips 
                    WHERE ip_address = ? 
                    AND (expires_at IS NULL OR expires_at > NOW() OR permanent = TRUE)";

            $result = $this->database->fetchOne($sql, [$ipAddress]);

            if ($result['count'] > 0) {
                $this->blockedIPs[$ipAddress] = true;
                return true;
            }
        } catch (Exception $e) {
            $this->logger->log("Error checking blocked IP: " . $e->getMessage(), 'error', 'security');
        }

        return false;
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit($ipAddress, $requestType)
    {
        $windowDuration = $this->securityConfig['rate_limit_window'] ?? 300; // 5 minutes
        $maxRequests = $this->securityConfig['rate_limit_max_requests'] ?? 100;

        try {
            // Clean old rate limit records
            $sql = "DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL ? SECOND)";
            $this->database->execute($sql, [$windowDuration]);

            // Check current rate
            $sql = "SELECT request_count, blocked FROM rate_limits 
                    WHERE ip_address = ? AND request_type = ? 
                    AND window_start >= DATE_SUB(NOW(), INTERVAL ? SECOND)";

            $result = $this->database->fetchOne($sql, [$ipAddress, $requestType, $windowDuration]);

            if ($result && $result['blocked']) {
                return false;
            }

            $currentCount = $result['request_count'] ?? 0;

            if ($currentCount >= $maxRequests) {
                // Block for this window
                $sql = "UPDATE rate_limits SET blocked = TRUE 
                        WHERE ip_address = ? AND request_type = ?";
                $this->database->execute($sql, [$ipAddress, $requestType]);

                return false;
            }

            // Increment counter
            if ($result) {
                $sql = "UPDATE rate_limits SET request_count = request_count + 1 
                        WHERE ip_address = ? AND request_type = ?";
                $this->database->execute($sql, [$ipAddress, $requestType]);
            } else {
                $sql = "INSERT INTO rate_limits (ip_address, request_type, request_count, window_duration) 
                        VALUES (?, ?, 1, ?)";
                $this->database->execute($sql, [$ipAddress, $requestType, $windowDuration]);
            }

            return true;
        } catch (Exception $e) {
            $this->logger->log("Error checking rate limit: " . $e->getMessage(), 'error', 'security');
            return true; // Allow on error
        }
    }

    /**
     * Log security event
     */
    private function logSecurityEvent($eventType, $severity, $ipAddress, $request, $description = '')
    {
        try {
            $sql = "INSERT INTO security_events (event_type, severity, ip_address, user_agent, request_uri, request_method, details) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $details = json_encode([
                'description' => $description,
                'post_params' => $request['post_params'] ?? [],
                'get_params' => $request['get_params'] ?? [],
                'headers' => $request['headers'] ?? []
            ]);

            $this->database->execute($sql, [
                $eventType,
                $severity,
                $ipAddress,
                $request['user_agent'] ?? '',
                $request['request_uri'] ?? '',
                $request['request_method'] ?? 'GET',
                $details
            ]);
        } catch (Exception $e) {
            $this->logger->log("Error logging security event: " . $e->getMessage(), 'error', 'security');
        }
    }

    /**
     * Get security dashboard data
     */
    public function getSecurityDashboard()
    {
        $dashboard = [
            'threat_summary' => $this->getThreatSummary(),
            'blocked_ips' => $this->getBlockedIPs(),
            'recent_events' => $this->getRecentSecurityEvents(),
            'vulnerabilities' => $this->getVulnerabilities(),
            'security_score' => $this->calculateSecurityScore()
        ];

        return $dashboard;
    }

    /**
     * Get threat summary
     */
    private function getThreatSummary()
    {
        $summary = [];

        try {
            // Events by type (last 24 hours)
            $sql = "SELECT event_type, COUNT(*) as count FROM security_events 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY event_type";

            $results = $this->database->fetchAll($sql);
            $summary['by_type'] = [];
            foreach ($results as $row) {
                $summary['by_type'][$row['event_type']] = $row['count'];
            }

            // Events by severity (last 24 hours)
            $sql = "SELECT severity, COUNT(*) as count FROM security_events 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY severity";

            $results = $this->database->fetchAll($sql);
            $summary['by_severity'] = [];
            foreach ($results as $row) {
                $summary['by_severity'][$row['severity']] = $row['count'];
            }

            // Total events
            $sql = "SELECT COUNT(*) as total FROM security_events 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

            $result = $this->database->fetchOne($sql);
            $summary['total_events'] = $result['total'] ?? 0;
        } catch (Exception $e) {
            $this->logger->log("Error getting threat summary: " . $e->getMessage(), 'error', 'security');
        }

        return $summary;
    }

    /**
     * Get blocked IPs
     */
    private function getBlockedIPs($limit = 50)
    {
        try {
            $sql = "SELECT * FROM blocked_ips 
                    WHERE expires_at IS NULL OR expires_at > NOW() 
                    ORDER BY blocked_at DESC 
                    LIMIT ?";

            return $this->database->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            $this->logger->log("Error getting blocked IPs: " . $e->getMessage(), 'error', 'security');
            return [];
        }
    }

    /**
     * Get recent security events
     */
    private function getRecentSecurityEvents($limit = 20)
    {
        try {
            $sql = "SELECT * FROM security_events 
                    ORDER BY created_at DESC 
                    LIMIT ?";

            return $this->database->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            $this->logger->log("Error getting security events: " . $e->getMessage(), 'error', 'security');
            return [];
        }
    }

    /**
     * Get vulnerabilities
     */
    private function getVulnerabilities($limit = 20)
    {
        try {
            $sql = "SELECT * FROM vulnerability_scans 
                    WHERE status = 'open' 
                    ORDER BY severity DESC, discovered_at DESC 
                    LIMIT ?";

            return $this->database->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            $this->logger->log("Error getting vulnerabilities: " . $e->getMessage(), 'error', 'security');
            return [];
        }
    }

    /**
     * Calculate security score
     */
    private function calculateSecurityScore()
    {
        $score = 100;

        try {
            // Deduct points for recent critical events
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE severity = 'critical' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

            $result = $this->database->fetchOne($sql);
            $criticalEvents = $result['count'] ?? 0;
            $score -= $criticalEvents * 20;

            // Deduct points for recent high events
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE severity = 'high' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

            $result = $this->database->fetchOne($sql);
            $highEvents = $result['count'] ?? 0;
            $score -= $highEvents * 10;

            // Deduct points for open vulnerabilities
            $sql = "SELECT COUNT(*) as count FROM vulnerability_scans 
                    WHERE status = 'open' AND severity IN ('high', 'critical')";

            $result = $this->database->fetchOne($sql);
            $vulnerabilities = $result['count'] ?? 0;
            $score -= $vulnerabilities * 15;

            return max(0, $score);
        } catch (Exception $e) {
            $this->logger->log("Error calculating security score: " . $e->getMessage(), 'error', 'security');
            return 50; // Default score
        }
    }

    /**
     * Load security configuration
     */
    private function loadSecurityConfig()
    {
        return [
            'rate_limit_window' => 300, // 5 minutes
            'rate_limit_max_requests' => 100,
            'max_failed_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'session_timeout' => 3600, // 1 hour
            'password_min_length' => 8,
            'require_strong_password' => true
        ];
    }

    /**
     * Load security data into memory
     */
    private function loadSecurityData()
    {
        try {
            // Load blocked IPs
            $sql = "SELECT ip_address FROM blocked_ips 
                    WHERE expires_at IS NULL OR expires_at > NOW() OR permanent = TRUE";

            $results = $this->database->fetchAll($sql);
            foreach ($results as $row) {
                $this->blockedIPs[$row['ip_address']] = true;
            }
        } catch (Exception $e) {
            $this->logger->log("Error loading security data: " . $e->getMessage(), 'error', 'security');
        }
    }

    /**
     * Get client IP address
     */
    private function getClientIP($request)
    {
        $headers = $request['headers'] ?? [];

        // Check for forwarded IP
        if (isset($headers['X-Forwarded-For']) && !empty($headers['X-Forwarded-For'])) {
            return $headers['X-Forwarded-For'];
        }

        if (isset($headers['X-Real-IP']) && !empty($headers['X-Real-IP'])) {
            return $headers['X-Real-IP'];
        }

        return $request['remote_addr'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Increment threat count for IP
     */
    private function incrementThreatCount($ipAddress, $threatType)
    {
        // This could be implemented with a separate table or in-memory tracking
        // For now, we'll just log it
        $this->logger->log("Threat count incremented for IP: $ipAddress, Type: $threatType", 'info', 'security');
    }

    /**
     * Get threat count for IP
     */
    private function getThreatCount($ipAddress, $severity)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE ip_address = ? AND severity = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

            $result = $this->database->fetchOne($sql, [$ipAddress, $severity]);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Clean up old security data
     */
    public function cleanupOldData($days = 30)
    {
        try {
            // Clean old security events
            $sql = "DELETE FROM security_events WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$days]);

            // Clean expired blocked IPs
            $sql = "DELETE FROM blocked_ips WHERE expires_at < NOW() AND permanent = FALSE";
            $this->database->execute($sql);

            // Clean old rate limits
            $sql = "DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 DAY)";
            $this->database->execute($sql);

            $this->logger->log("Security data cleanup completed", 'info', 'security');
        } catch (Exception $e) {
            $this->logger->log("Error cleaning security data: " . $e->getMessage(), 'error', 'security');
        }
    }
}
