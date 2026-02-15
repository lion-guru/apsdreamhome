<?php

namespace App\Services\Legacy;
/**
 * Advanced Security Monitoring and Threat Detection System
 * Provides comprehensive security monitoring, anomaly detection, and threat prevention
 */
class SecurityMonitor {
    // Threat detection thresholds
    private const THRESHOLDS = [
        'login_attempts' => 5,
        'ip_block_duration' => 3600, // 1 hour
        'suspicious_activity_score' => 75,
        'rate_limit' => [
            'requests_per_minute' => 100,
            'window_seconds' => 60
        ]
    ];

    /**
     * Track login attempts and implement brute force protection
     * @param string $username
     * @param bool $successful
     * @return bool
     */
    public static function trackLoginAttempt($username, $successful = false) {
        try {
            $db = \App\Core\App::database();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            // Begin transaction
            $db->beginTransaction();

            // Check if IP is already blocked
            $sql = "
                SELECT blocked_until
                FROM ip_block_list
                WHERE ip_address = ? AND blocked_until > NOW()
            ";
            $blockData = $db->fetch($sql, [$ip]);

            if ($blockData) {
                // IP is currently blocked
                AdminLogger::securityAlert('BLOCKED_IP_LOGIN_ATTEMPT', [
                    'username' => $username,
                    'ip' => $ip
                ]);
                $db->commit();
                return false;
            }

            // Track login attempt
            $sql = "
                INSERT INTO login_attempts
                (username, ip_address, attempt_time, successful)
                VALUES (?, ?, NOW(), ?)
            ";
            $db->execute($sql, [$username, $ip, $successful ? 1 : 0]);

            // Count failed attempts in last hour
            $sql = "
                SELECT COUNT(*) as failed_attempts
                FROM login_attempts
                WHERE username = ?
                AND ip_address = ?
                AND successful = 0
                AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ";
            $attemptsData = $db->fetch($sql, [$username, $ip]);

            // Check if brute force threshold is reached
            if ($attemptsData['failed_attempts'] >= self::THRESHOLDS['login_attempts']) {
                // Block IP
                $sql = "
                    INSERT INTO ip_block_list
                    (ip_address, blocked_until, block_reason)
                    VALUES (?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 'BRUTE_FORCE')
                    ON DUPLICATE KEY UPDATE
                    blocked_until = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                ";
                $db->execute($sql, [$ip]);

                // Log security alert
                AdminLogger::securityAlert('IP_BLOCKED_BRUTE_FORCE', [
                    'username' => $username,
                    'ip' => $ip,
                    'failed_attempts' => $attemptsData['failed_attempts']
                ]);

                $db->commit();
                return false;
            }

            // Commit transaction
            $db->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            if (isset($db)) $db->rollBack();

            AdminLogger::logError('LOGIN_ATTEMPT_TRACKING_ERROR', [
                'message' => $e->getMessage(),
                'username' => $username
            ]);

            return false;
        }
    }

    /**
     * Detect and prevent potential SQL injection attempts
     * @param string $query
     * @param array $params
     * @return bool
     */
    public static function detectSQLInjection($query, $params = []) {
        // Suspicious SQL keywords and patterns
        $sqlInjectionPatterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER)\b/i',
            '/--/',
            '/\/\*.*\*\//',
            '/\b(AND|OR)\s+1\s*=\s*1/i',
            '/\bOR\s+\'1\'=\'1/i'
        ];

        // Check query and parameters
        foreach ($sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                self::logThreat('SQL_INJECTION_ATTEMPT', [
                    'query' => $query,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return true;
            }

            // Check parameters
            foreach ($params as $param) {
                if (is_string($param) && preg_match($pattern, $param)) {
                    self::logThreat('SQL_INJECTION_PARAMETER', [
                        'parameter' => $param,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                    ]);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Implement rate limiting for API and admin endpoints
     * @param string $identifier
     * @return bool
     */
    public static function checkRateLimit($identifier) {
        try {
            $db = \App\Core\App::database();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            // Clean up old rate limit entries
            $sql = "
                DELETE FROM rate_limit
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? SECOND)
            ";
            $db->execute($sql, [self::THRESHOLDS['rate_limit']['window_seconds']]);

            // Count recent requests
            $sql = "
                SELECT COUNT(*) as request_count
                FROM rate_limit
                WHERE identifier = ?
                AND ip_address = ?
                AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ";
            $rateData = $db->fetch($sql, [
                $identifier,
                $ip,
                self::THRESHOLDS['rate_limit']['window_seconds']
            ]);

            // Check if rate limit exceeded
            if ($rateData['request_count'] >= self::THRESHOLDS['rate_limit']['requests_per_minute']) {
                // Log rate limit violation
                self::logThreat('RATE_LIMIT_EXCEEDED', [
                    'identifier' => $identifier,
                    'ip' => $ip,
                    'request_count' => $rateData['request_count']
                ]);

                return false;
            }

            // Record request
            $sql = "
                INSERT INTO rate_limit
                (identifier, ip_address, timestamp)
                VALUES (?, ?, NOW())
            ";
            $db->execute($sql, [$identifier, $ip]);

            return true;
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Fail open for rate limiting
        }
    }

    /**
     * Detect potential XSS attempts
     * @param mixed $input
     * @return bool
     */
    public static function detectXSSAttempt($input) {
        if (!is_string($input)) {
            return false;
        }

        // XSS detection patterns
        $xssPatterns = [
            '/<script\b[^>]*>/i',
            '/\bon\w+\s*=\s*[\'"].*?[\'"]/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onerror\s*=/i'
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::logThreat('XSS_ATTEMPT', [
                    'input' => $input,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Log potential security threat
     * @param string $threatType
     * @param array $data
     */
    private static function logThreat($threatType, $data) {
        try {
            $db = \App\Core\App::database();
            $sql = "
                INSERT INTO security_threats
                (threat_type, threat_data, ip_address, created_at)
                VALUES (?, ?, ?, NOW())
            ";
            $db->execute($sql, [
                $threatType,
                json_encode($data),
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log("Security threat logging error: " . $e->getMessage());
        }
    }

    /**
     * Perform comprehensive security scan
     * @return array
     */
    public static function performSecurityScan() {
        $securityIssues = [];

        try {
            $db = \App\Core\App::database();

            // Check for recent suspicious login attempts
            $sql = "
                SELECT COUNT(*) as suspicious_logins
                FROM login_attempts
                WHERE successful = 0
                AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ";
            $loginData = $db->fetch($sql);

            if ($loginData['suspicious_logins'] > 10) {
                $securityIssues[] = [
                    'type' => 'LOGIN_ATTEMPTS',
                    'severity' => 'HIGH',
                    'description' => 'Unusually high number of failed login attempts'
                ];
            }

            // Check for blocked IPs
            $sql = "
                SELECT COUNT(*) as blocked_ips
                FROM ip_block_list
                WHERE blocked_until > NOW()
            ";
            $blockData = $db->fetch($sql);

            if ($blockData['blocked_ips'] > 0) {
                $securityIssues[] = [
                    'type' => 'BLOCKED_IPS',
                    'severity' => 'MEDIUM',
                    'description' => 'Active IP blocks detected'
                ];
            }

            return $securityIssues;
        } catch (PDOException $e) {
            AdminLogger::logError('SECURITY_SCAN_ERROR', [
                'message' => $e->getMessage()
            ]);

            return [];
        }
    }
}

// Global helper functions
function track_login_attempt($username, $successful = false) {
    return SecurityMonitor::trackLoginAttempt($username, $successful);
}

function detect_sql_injection($query, $params = []) {
    return SecurityMonitor::detectSQLInjection($query, $params);
}

function check_rate_limit($identifier) {
    return SecurityMonitor::checkRateLimit($identifier);
}

function detect_xss($input) {
    return SecurityMonitor::detectXSSAttempt($input);
}
