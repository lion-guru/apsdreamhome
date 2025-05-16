<?php
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
            $pdo = DatabaseConnection::getInstance();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            // Begin transaction
            $pdo->beginTransaction();

            // Check if IP is already blocked
            $stmt = $pdo->prepare("
                SELECT blocked_until 
                FROM ip_block_list 
                WHERE ip_address = ? AND blocked_until > NOW()
            ");
            $stmt->execute([$ip]);
            $blockData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($blockData) {
                // IP is currently blocked
                AdminLogger::securityAlert('BLOCKED_IP_LOGIN_ATTEMPT', [
                    'username' => $username,
                    'ip' => $ip
                ]);
                $pdo->commit();
                return false;
            }

            // Track login attempt
            $stmt = $pdo->prepare("
                INSERT INTO login_attempts 
                (username, ip_address, attempt_time, successful) 
                VALUES (?, ?, NOW(), ?)
            ");
            $stmt->execute([$username, $ip, $successful ? 1 : 0]);

            // Count failed attempts in last hour
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as failed_attempts 
                FROM login_attempts 
                WHERE username = ? 
                AND ip_address = ? 
                AND successful = 0 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$username, $ip]);
            $attemptsData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if brute force threshold is reached
            if ($attemptsData['failed_attempts'] >= self::THRESHOLDS['login_attempts']) {
                // Block IP
                $stmt = $pdo->prepare("
                    INSERT INTO ip_block_list 
                    (ip_address, blocked_until, block_reason) 
                    VALUES (?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 'BRUTE_FORCE')
                    ON DUPLICATE KEY UPDATE 
                    blocked_until = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                ");
                $stmt->execute([$ip]);

                // Log security alert
                AdminLogger::securityAlert('IP_BLOCKED_BRUTE_FORCE', [
                    'username' => $username,
                    'ip' => $ip,
                    'failed_attempts' => $attemptsData['failed_attempts']
                ]);

                $pdo->commit();
                return false;
            }

            // Commit transaction
            $pdo->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $pdo->rollBack();

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
            $pdo = DatabaseConnection::getInstance();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            // Clean up old rate limit entries
            $stmt = $pdo->prepare("
                DELETE FROM rate_limit 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([self::THRESHOLDS['rate_limit']['window_seconds']]);

            // Count recent requests
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as request_count 
                FROM rate_limit 
                WHERE identifier = ? 
                AND ip_address = ? 
                AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([
                $identifier, 
                $ip, 
                self::THRESHOLDS['rate_limit']['window_seconds']
            ]);
            $rateData = $stmt->fetch(PDO::FETCH_ASSOC);

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
            $stmt = $pdo->prepare("
                INSERT INTO rate_limit 
                (identifier, ip_address, timestamp) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$identifier, $ip]);

            return true;
        } catch (PDOException $e) {
            AdminLogger::logError('RATE_LIMIT_ERROR', [
                'message' => $e->getMessage(),
                'identifier' => $identifier
            ]);
            return false;
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
     * Log security threats
     * @param string $threatType
     * @param array $details
     */
    private static function logThreat($threatType, $details = []) {
        AdminLogger::securityAlert($threatType, array_merge([
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_id' => $_SESSION['admin_user_id'] ?? 'Unknown'
        ], $details));
    }

    /**
     * Perform comprehensive security scan
     * @return array
     */
    public static function performSecurityScan() {
        $securityIssues = [];

        try {
            $pdo = DatabaseConnection::getInstance();

            // Check for recent suspicious login attempts
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as suspicious_logins 
                FROM login_attempts 
                WHERE successful = 0 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute();
            $loginData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($loginData['suspicious_logins'] > 10) {
                $securityIssues[] = [
                    'type' => 'LOGIN_ATTEMPTS',
                    'severity' => 'HIGH',
                    'description' => 'Unusually high number of failed login attempts'
                ];
            }

            // Check for blocked IPs
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as blocked_ips 
                FROM ip_block_list 
                WHERE blocked_until > NOW()
            ");
            $stmt->execute();
            $blockData = $stmt->fetch(PDO::FETCH_ASSOC);

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
