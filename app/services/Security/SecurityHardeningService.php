<?php

namespace App\Services\Security;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Security Hardening Service
 * Handles comprehensive security hardening with proper MVC patterns
 */
class SecurityHardeningService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $securityRules = [];

    // Security levels
    public const LEVEL_LOW = 1;
    public const LEVEL_MEDIUM = 2;
    public const LEVEL_HIGH = 3;
    public const LEVEL_CRITICAL = 4;

    // Protection types
    public const PROTECTION_SQL_INJECTION = 'sql_injection';
    public const PROTECTION_XSS = 'xss';
    public const PROTECTION_CSRF = 'csrf';
    public const PROTECTION_FILE_UPLOAD = 'file_upload';
    public const PROTECTION_RATE_LIMITING = 'rate_limiting';
    public const PROTECTION_IP_BLOCKING = 'ip_blocking';
    public const PROTECTION_INPUT_VALIDATION = 'input_validation';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'auto_harden' => true,
            'monitoring_enabled' => true,
            'alert_threshold' => 5,
            'block_duration' => 3600, // 1 hour
            'max_failed_attempts' => 5,
            'session_timeout' => 1800, // 30 minutes
            'password_policy_enabled' => true,
            'two_factor_enabled' => false,
            'ip_whitelist_enabled' => false,
            'security_headers_enabled' => true
        ], $config);
        
        $this->initializeSecurityTables();
        $this->loadSecurityRules();
    }

    /**
     * Apply security hardening
     */
    public function applySecurityHardening(): array
    {
        try {
            $hardening = [];
            $improvements = 0;

            // SQL Injection protection
            if ($this->applySQLInjectionProtection()) {
                $hardening[] = 'SQL Injection protection applied';
                $improvements++;
            }

            // XSS protection
            if ($this->applyXSSProtection()) {
                $hardening[] = 'XSS protection applied';
                $improvements++;
            }

            // CSRF protection
            if ($this->applyCSRFProtection()) {
                $hardening[] = 'CSRF protection applied';
                $improvements++;
            }

            // File upload protection
            if ($this->applyFileUploadProtection()) {
                $hardening[] = 'File upload protection applied';
                $improvements++;
            }

            // Rate limiting
            if ($this->applyRateLimiting()) {
                $hardening[] = 'Rate limiting applied';
                $improvements++;
            }

            // IP blocking
            if ($this->applyIPBlocking()) {
                $hardening[] = 'IP blocking applied';
                $improvements++;
            }

            // Input validation
            if ($this->applyInputValidation()) {
                $hardening[] = 'Input validation applied';
                $improvements++;
            }

            // Security headers
            if ($this->applySecurityHeaders()) {
                $hardening[] = 'Security headers applied';
                $improvements++;
            }

            $this->logger->info("Security hardening completed", [
                'hardening' => $hardening,
                'improvements' => $improvements
            ]);

            return [
                'success' => true,
                'message' => "Security hardening completed",
                'hardening' => $hardening,
                'improvements' => $improvements
            ];

        } catch (\Exception $e) {
            $this->logger->error("Security hardening failed", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Security hardening failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check security status
     */
    public function getSecurityStatus(): array
    {
        try {
            $status = [];

            // Get active security rules
            $sql = "SELECT * FROM security_rules WHERE enabled = 1";
            $rules = $this->db->fetchAll($sql);
            $status['active_rules'] = $rules;

            // Get security incidents
            $incidents = $this->getSecurityIncidents();
            $status['incidents'] = $incidents;

            // Get blocked IPs
            $blockedIPs = $this->getBlockedIPs();
            $status['blocked_ips'] = $blockedIPs;

            // Get security score
            $status['security_score'] = $this->calculateSecurityScore();

            // Get recommendations
            $status['recommendations'] = $this->getSecurityRecommendations();

            return $status;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get security status", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Block IP address
     */
    public function blockIP(string $ip, string $reason = '', int $duration = null): array
    {
        try {
            $duration = $duration ?? $this->config['block_duration'];
            $expiresAt = date('Y-m-d H:i:s', time() + $duration);

            $sql = "INSERT INTO blocked_ips 
                    (ip_address, reason, expires_at, created_by, created_at) 
                    VALUES (?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    reason = ?, expires_at = ?, updated_at = NOW()";
            
            $this->db->execute($sql, [
                $ip, $reason, $expiresAt, $this->getCurrentUserId(),
                $reason, $expiresAt, $this->getCurrentUserId()
            ]);

            $this->logger->warning("IP blocked", [
                'ip' => $ip,
                'reason' => $reason,
                'duration' => $duration
            ]);

            return [
                'success' => true,
                'message' => 'IP blocked successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to block IP", [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to block IP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Unblock IP address
     */
    public function unblockIP(string $ip): array
    {
        try {
            $sql = "DELETE FROM blocked_ips WHERE ip_address = ?";
            $affectedRows = $this->db->execute($sql, [$ip]);

            if ($affectedRows > 0) {
                $this->logger->info("IP unblocked", ['ip' => $ip]);
                return [
                    'success' => true,
                    'message' => 'IP unblocked successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'IP not found in blocked list'
                ];
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to unblock IP", [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to unblock IP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log security incident
     */
    public function logSecurityIncident(string $type, string $description, array $data = [], int $severity = self::LEVEL_MEDIUM): array
    {
        try {
            $sql = "INSERT INTO security_incidents 
                    (incident_type, description, incident_data, severity, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $type,
                $description,
                json_encode($data),
                $severity,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            // Check if alert threshold is reached
            $this->checkAlertThreshold($type);

            $this->logger->warning("Security incident logged", [
                'type' => $type,
                'severity' => $severity,
                'description' => $description
            ]);

            return [
                'success' => true,
                'message' => 'Security incident logged successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to log security incident", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to log security incident: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get security statistics
     */
    public function getSecurityStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total incidents
            $sql = "SELECT COUNT(*) as total FROM security_incidents";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            $stats['total_incidents'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Incidents by type
            $typeSql = "SELECT incident_type, COUNT(*) as count FROM security_incidents";
            $typeParams = [];
            
            if (!empty($filters['date_from'])) {
                $typeSql .= " WHERE created_at >= ?";
                $typeParams[] = $filters['date_from'];
            }
            
            $typeSql .= " GROUP BY incident_type";
            
            $typeStats = $this->db->fetchAll($typeSql, $typeParams);
            $stats['by_type'] = [];
            foreach ($typeStats as $stat) {
                $stats['by_type'][$stat['incident_type']] = $stat['count'];
            }

            // Incidents by severity
            $severitySql = "SELECT severity, COUNT(*) as count FROM security_incidents";
            $severityParams = [];
            
            if (!empty($filters['date_from'])) {
                $severitySql .= " WHERE created_at >= ?";
                $severityParams[] = $filters['date_from'];
            }
            
            $severitySql .= " GROUP BY severity";
            
            $severityStats = $this->db->fetchAll($severitySql, $severityParams);
            $stats['by_severity'] = [];
            foreach ($severityStats as $stat) {
                $stats['by_severity'][$stat['severity']] = $stat['count'];
            }

            // Blocked IPs
            $stats['blocked_ips_count'] = $this->db->fetchOne("SELECT COUNT(*) FROM blocked_ips WHERE expires_at > NOW()") ?? 0;

            // Recent incidents
            $stats['recent_incidents'] = $this->db->fetchAll("
                SELECT * FROM security_incidents 
                ORDER BY created_at DESC 
                LIMIT 20
            ");

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get security stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeSecurityTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS security_rules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rule_type VARCHAR(100) NOT NULL,
                rule_name VARCHAR(255) NOT NULL,
                rule_config JSON,
                enabled BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (rule_type),
                INDEX idx_enabled (enabled)
            )",
            
            "CREATE TABLE IF NOT EXISTS security_incidents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                incident_type VARCHAR(100) NOT NULL,
                description TEXT,
                incident_data JSON,
                severity INT NOT NULL DEFAULT 2,
                ip_address VARCHAR(45),
                user_agent TEXT,
                resolved BOOLEAN DEFAULT FALSE,
                resolved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (incident_type),
                INDEX idx_severity (severity),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS blocked_ips (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                reason TEXT,
                expires_at TIMESTAMP NOT NULL,
                created_by VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_ip (ip_address),
                INDEX idx_expires_at (expires_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function loadSecurityRules(): void
    {
        $this->securityRules = [
            self::PROTECTION_SQL_INJECTION => [
                'enabled' => true,
                'patterns' => ['UNION', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'EXEC', 'SCRIPT'],
                'action' => 'block'
            ],
            self::PROTECTION_XSS => [
                'enabled' => true,
                'patterns' => ['<script', 'javascript:', 'onerror=', 'onload=', '<img', '<iframe'],
                'action' => 'sanitize'
            ],
            self::PROTECTION_CSRF => [
                'enabled' => true,
                'token_length' => 32,
                'token_expiry' => 3600,
                'action' => 'verify'
            ],
            self::PROTECTION_FILE_UPLOAD => [
                'enabled' => true,
                'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
                'max_size' => 10 * 1024 * 1024, // 10MB
                'scan_viruses' => true,
                'action' => 'validate'
            ],
            self::PROTECTION_RATE_LIMITING => [
                'enabled' => true,
                'max_requests' => 100,
                'window_minutes' => 1,
                'block_duration' => 900, // 15 minutes
                'action' => 'limit'
            ],
            self::PROTECTION_IP_BLOCKING => [
                'enabled' => true,
                'auto_block' => true,
                'block_threshold' => 10,
                'block_duration' => 3600, // 1 hour
                'action' => 'block'
            ],
            self::PROTECTION_INPUT_VALIDATION => [
                'enabled' => true,
                'sanitize_html' => true,
                'validate_email' => true,
                'validate_phone' => true,
                'max_length' => 1000,
                'action' => 'validate'
            ]
        ];
    }

    private function applySQLInjectionProtection(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_SQL_INJECTION];
        if (!$rule['enabled']) return false;

        // Save rule to database
        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_SQL_INJECTION,
            'SQL Injection Protection',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applyXSSProtection(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_XSS];
        if (!$rule['enabled']) return false;

        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_XSS,
            'XSS Protection',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applyCSRFProtection(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_CSRF];
        if (!$rule['enabled']) return false;

        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_CSRF,
            'CSRF Protection',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applyFileUploadProtection(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_FILE_UPLOAD];
        if (!$rule['enabled']) return false;

        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_FILE_UPLOAD,
            'File Upload Protection',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applyRateLimiting(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_RATE_LIMITING];
        if (!$rule['enabled']) return false;

        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_RATE_LIMITING,
            'Rate Limiting',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applyIPBlocking(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_IP_BLOCKING];
        if (!$rule['enabled']) return false;

        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_IP_BLOCKING,
            'IP Blocking',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applyInputValidation(): bool
    {
        $rule = $this->securityRules[self::PROTECTION_INPUT_VALIDATION];
        if (!$rule['enabled']) return false;

        $sql = "INSERT INTO security_rules 
                (rule_type, rule_name, rule_config, enabled, created_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                rule_config = ?, enabled = ?, updated_at = NOW()";
        
        $this->db->execute($sql, [
            self::PROTECTION_INPUT_VALIDATION,
            'Input Validation',
            json_encode($rule),
            $rule['enabled'] ? 1 : 0,
            json_encode($rule),
            $rule['enabled'] ? 1 : 0
        ]);

        return true;
    }

    private function applySecurityHeaders(): bool
    {
        // Security headers are applied at web server level
        // This method logs the application of security headers
        $headers = [
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'",
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];

        foreach ($headers as $header => $value) {
            $sql = "INSERT INTO security_rules 
                    (rule_type, rule_name, rule_config, enabled, created_at) 
                    VALUES (?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    rule_config = ?, enabled = ?, updated_at = NOW()";
            
            $this->db->execute($sql, [
                'security_headers',
                "Header: {$header}",
                json_encode(['header' => $header, 'value' => $value]),
                1,
                json_encode(['header' => $header, 'value' => $value]),
                1
            ]);
        }

        return true;
    }

    private function getSecurityIncidents(): array
    {
        $sql = "SELECT * FROM security_incidents 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                ORDER BY created_at DESC 
                LIMIT 50";
        
        return $this->db->fetchAll($sql);
    }

    private function getBlockedIPs(): array
    {
        $sql = "SELECT * FROM blocked_ips 
                WHERE expires_at > NOW() 
                ORDER BY created_at DESC 
                LIMIT 100";
        
        return $this->db->fetchAll($sql);
    }

    private function calculateSecurityScore(): int
    {
        $rules = $this->db->fetchAll("SELECT * FROM security_rules WHERE enabled = 1");
        $incidents = $this->db->fetchOne("SELECT COUNT(*) FROM security_incidents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)") ?? 0;
        
        $score = 100; // Start with perfect score
        
        // Deduct points for incidents
        $score -= min(50, $incidents * 5);
        
        // Add points for active security rules
        $score += count($rules) * 2;
        
        return max(0, min(100, $score));
    }

    private function getSecurityRecommendations(): array
    {
        $recommendations = [];
        
        // Check for missing security rules
        $activeRules = $this->db->fetchAll("SELECT rule_type FROM security_rules WHERE enabled = 1");
        $activeRuleTypes = array_column($activeRules, 'rule_type');
        
        $requiredRules = [
            self::PROTECTION_SQL_INJECTION,
            self::PROTECTION_XSS,
            self::PROTECTION_CSRF,
            self::PROTECTION_FILE_UPLOAD
        ];
        
        foreach ($requiredRules as $rule) {
            if (!in_array($rule, $activeRuleTypes)) {
                $recommendations[] = "Enable {$rule} protection";
            }
        }
        
        // Check for recent high-severity incidents
        $highSeverityCount = $this->db->fetchOne("
            SELECT COUNT(*) FROM security_incidents 
            WHERE severity >= ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ", [self::LEVEL_HIGH]) ?? 0;
        
        if ($highSeverityCount > 5) {
            $recommendations[] = "Review recent security incidents - high activity detected";
        }
        
        return $recommendations;
    }

    private function checkAlertThreshold(string $incidentType): void
    {
        $count = $this->db->fetchOne("
            SELECT COUNT(*) FROM security_incidents 
            WHERE incident_type = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ", [$incidentType]) ?? 0;
        
        if ($count >= $this->config['alert_threshold']) {
            $this->logger->critical("Security alert threshold reached", [
                'incident_type' => $incidentType,
                'count' => $count,
                'threshold' => $this->config['alert_threshold']
            ]);
        }
    }

    private function getCurrentUserId(): string
    {
        return $_SESSION['user_id'] ?? 'system';
    }
}
