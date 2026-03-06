<?php

namespace App\Services\Legacy;

/**
 * Security Hardening System - APS Dream Homes
 * Advanced security features and protection
 */

class SecurityHardening {
    private $db;
    private $securityConfig;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->initSecurity();
    }

    /**
     * Initialize security system
     */
    private function initSecurity() {
        // Create security tables
        $this->createSecurityTables();

        // Setup security monitoring
        $this->setupSecurityMonitoring();
    }

    /**
     * Create security database tables
     */
    private function createSecurityTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS security_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                action VARCHAR(100),
                details JSON,
                risk_level ENUM('low', 'medium', 'high', 'critical'),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_ip (ip_address),
                INDEX idx_action (action),
                INDEX idx_risk (risk_level),
                INDEX idx_time (created_at)
            )",

            "CREATE TABLE IF NOT EXISTS security_blacklist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) UNIQUE,
                reason VARCHAR(255),
                blacklisted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT 1,
                INDEX idx_ip (ip_address),
                INDEX idx_expires (expires_at)
            )",

            "CREATE TABLE IF NOT EXISTS security_rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45),
                action_type VARCHAR(100),
                request_count INT DEFAULT 1,
                window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                window_duration INT DEFAULT 3600,
                is_blocked BOOLEAN DEFAULT 0,
                INDEX idx_ip_action (ip_address, action_type),
                INDEX idx_window (window_start)
            )",

            "CREATE TABLE IF NOT EXISTS security_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                user_id INT,
                ip_address VARCHAR(45),
                user_agent_hash VARCHAR(255),
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_valid BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_session (session_id),
                INDEX idx_user (user_id),
                INDEX idx_activity (last_activity)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    /**
     * Setup security monitoring
     */
    private function setupSecurityMonitoring() {
        // Initialize rate limiting
        $this->initRateLimiting();

        // Setup brute force protection
        $this->initBruteForceProtection();

        // Setup XSS protection
        $this->initXSSProtection();

        // Setup SQL injection protection
        $this->initSQLInjectionProtection();
    }

    /**
     * Check rate limiting
     */
    public function checkRateLimit($action, $maxRequests = 100, $windowDuration = 3600) {
        $ipAddress = $this->getClientIP();

        // Clean old entries
        $this->cleanRateLimitEntries($ipAddress, $action);

        // Check current count
        $sql = "SELECT request_count, is_blocked FROM security_rate_limits
                WHERE ip_address = ? AND action_type = ? AND is_blocked = 0";
        $rateLimit = $this->db->fetch($sql, [$ipAddress, $action]);

        if ($rateLimit) {
            if ($rateLimit['request_count'] >= $maxRequests) {
                // Block the IP
                $this->blockIP($ipAddress, "Rate limit exceeded for action: $action");
                return false;
            }

            // Increment count
            $sql = "UPDATE security_rate_limits SET request_count = request_count + 1 WHERE ip_address = ? AND action_type = ?";
            $this->db->execute($sql, [$ipAddress, $action]);
        } else {
            // Create new entry
            $sql = "INSERT INTO security_rate_limits (ip_address, action_type, window_duration) VALUES (?, ?, ?)";
            $this->db->execute($sql, [$ipAddress, $action, $windowDuration]);
        }

        return true;
    }

    /**
     * Clean rate limit entries
     */
    private function cleanRateLimitEntries($ipAddress, $action) {
        $sql = "DELETE FROM security_rate_limits
                WHERE ip_address = ? AND action_type = ?
                AND window_start < DATE_SUB(NOW(), INTERVAL window_duration SECOND)";
        $this->db->execute($sql, [$ipAddress, $action]);
    }

    /**
     * Block IP address
     */
    private function blockIP($ipAddress, $reason) {
        $sql = "INSERT INTO security_blacklist (ip_address, reason) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE reason = VALUES(reason), blacklisted_at = NOW()";
        $this->db->execute($sql, [$ipAddress, $reason]);

        // Log security event
        $this->logSecurityEvent(null, $ipAddress, 'IP_BLOCKED', ['reason' => $reason], 'high');
    }

    /**
     * Check if IP is blacklisted
     */
    public function isIPBlacklisted($ipAddress) {
        $sql = "SELECT COUNT(*) as count FROM security_blacklist
                WHERE ip_address = ? AND is_active = 1
                AND (expires_at IS NULL OR expires_at > NOW())";
        $result = $this->db->fetch($sql, [$ipAddress]);

        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Validate session security
     */
    public function validateSession($sessionId, $userId) {
        $ipAddress = $this->getClientIP();
        $userAgentHash = md5($_SERVER['HTTP_USER_AGENT'] ?? '');

        $sql = "SELECT * FROM security_sessions
                WHERE session_id = ? AND user_id = ? AND is_valid = 1";
        $session = $this->db->fetch($sql, [$sessionId, $userId]);

        if ($session) {
            // Check IP and user agent
            if ($session['ip_address'] !== $ipAddress || $session['user_agent_hash'] !== $userAgentHash) {
                // Session hijacking detected
                $this->invalidateSession($sessionId);
                $this->logSecurityEvent($userId, $ipAddress, 'SESSION_HIJACK', [], 'critical');
                return false;
            }

            // Update last activity
            $sql = "UPDATE security_sessions SET last_activity = NOW() WHERE session_id = ?";
            $this->db->execute($sql, [$sessionId]);

            return true;
        }

        return false;
    }

    /**
     * Invalidate session
     */
    private function invalidateSession($sessionId) {
        $sql = "UPDATE security_sessions SET is_valid = 0 WHERE session_id = ?";
        $this->db->execute($sql, [$sessionId]);
    }

    /**
     * Sanitize input
     */
    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        // Remove HTML tags
        $input = strip_tags($input);

        // Escape special characters
        $input = h($input);

        // Remove SQL injection patterns
        $sqlPatterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)(\s|$)/i',
            '/(\s|^)(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/(\s|^)(--|#|\/\*|\*\/)/'
        ];

        foreach ($sqlPatterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        return trim($input);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent($userId, $ipAddress, $action, $details = [], $riskLevel = 'medium') {
        $sql = "INSERT INTO security_logs (user_id, ip_address, user_agent, action, details, risk_level)
                VALUES (?, ?, ?, ?, ?, ?)";

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $detailsJson = json_encode($details);
        $this->db->execute($sql, [$userId, $ipAddress, $userAgent, $action, $detailsJson, $riskLevel]);
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

    /**
     * Initialize rate limiting
     */
    private function initRateLimiting() {
        // Rate limiting is implemented in checkRateLimit method
    }

    /**
     * Initialize brute force protection
     */
    private function initBruteForceProtection() {
        // Brute force protection is implemented through rate limiting and IP blacklisting
    }

    /**
     * Initialize XSS protection
     */
    private function initXSSProtection() {
        // XSS protection is implemented in sanitizeInput method
    }

    /**
     * Initialize SQL injection protection
     */
    private function initSQLInjectionProtection() {
        // SQL injection protection is implemented in sanitizeInput method
    }
}

// Initialize security system
$security = new SecurityHardening();
?>
