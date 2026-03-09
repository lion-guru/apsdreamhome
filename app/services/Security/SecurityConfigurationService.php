<?php

namespace App\Services\Security;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Security Configuration Service
 * Handles comprehensive security configuration with proper MVC patterns
 */
class SecurityConfigurationService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $policies = [];

    // Security levels
    public const LEVEL_LOW = 1;
    public const LEVEL_MEDIUM = 2;
    public const LEVEL_HIGH = 3;
    public const LEVEL_CRITICAL = 4;

    // Configuration categories
    public const CATEGORY_PASSWORD = 'password';
    public const CATEGORY_SESSION = 'session';
    public const CATEGORY_TWO_FACTOR = 'two_factor';
    public const CATEGORY_IP_RESTRICTION = 'ip_restriction';
    public const CATEGORY_SECURITY_HEADERS = 'security_headers';
    public const CATEGORY_ENCRYPTION = 'encryption';
    public const CATEGORY_AUDIT = 'audit';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'auto_apply_policies' => true,
            'audit_log_changes' => true,
            'config_cache_ttl' => 300, // 5 minutes
            'max_login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'password_min_length' => 12,
            'session_timeout' => 1800, // 30 minutes
            'two_factor_enabled' => true,
            'ip_whitelist_enabled' => false
        ], $config);
        
        $this->initializeSecurityTables();
        $this->loadDefaultPolicies();
    }

    /**
     * Get security configuration
     */
    public function getConfiguration(string $category = null, string $key = null): array
    {
        try {
            $sql = "SELECT * FROM security_configurations WHERE 1=1";
            $params = [];

            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            if ($key) {
                $sql .= " AND config_key = ?";
                $params[] = $key;
            }

            $sql .= " ORDER BY category, config_key";

            $configurations = $this->db->fetchAll($sql, $params);
            
            $result = [];
            foreach ($configurations as $config) {
                $value = json_decode($config['config_value'], true) ?? $config['config_value'];
                
                if ($category && $key) {
                    return $value;
                }
                
                if (!isset($result[$config['category']])) {
                    $result[$config['category']] = [];
                }
                
                $result[$config['category']][$config['config_key']] = $value;
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get security configuration", [
                'category' => $category,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Set security configuration
     */
    public function setConfiguration(string $category, string $key, $value, bool $isSensitive = false): array
    {
        try {
            // Validate configuration
            $validation = $this->validateConfiguration($category, $key, $value);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Configuration validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Log change if auditing is enabled
            if ($this->config['audit_log_changes']) {
                $oldValue = $this->getConfiguration($category, $key);
                $this->auditConfigurationChange($category, $key, $oldValue, $value);
            }

            // Save configuration
            $sql = "INSERT INTO security_configurations 
                    (category, config_key, config_value, is_sensitive, updated_by, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    config_value = ?, is_sensitive = ?, updated_by = ?, updated_at = NOW()";
            
            $this->db->execute($sql, [
                $category, $key, json_encode($value), $isSensitive ? 1 : 0, 
                $this->getCurrentUserId(),
                json_encode($value), $isSensitive ? 1 : 0, 
                $this->getCurrentUserId()
            ]);

            // Apply configuration if auto-apply is enabled
            if ($this->config['auto_apply_policies']) {
                $this->applySecurityConfiguration($category, $key, $value);
            }

            $this->logger->info("Security configuration updated", [
                'category' => $category,
                'key' => $key,
                'is_sensitive' => $isSensitive
            ]);

            return [
                'success' => true,
                'message' => 'Security configuration updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to set security configuration", [
                'category' => $category,
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Apply security policies
     */
    public function applySecurityPolicies(): array
    {
        try {
            $appliedPolicies = [];
            $improvements = 0;

            // Get all configurations
            $configurations = $this->getConfiguration();

            foreach ($configurations as $category => $categoryConfig) {
                foreach ($categoryConfig as $key => $value) {
                    $result = $this->applySecurityConfiguration($category, $key, $value);
                    
                    if ($result['success']) {
                        $appliedPolicies[] = "Applied {$category}.{$key}";
                        $improvements++;
                    }
                }
            }

            $this->logger->info("Security policies applied", [
                'policies_applied' => $appliedPolicies,
                'improvements' => $improvements
            ]);

            return [
                'success' => true,
                'message' => "Applied {$improvements} security policies",
                'policies_applied' => $appliedPolicies,
                'improvements' => $improvements
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to apply security policies", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to apply security policies: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate security configuration
     */
    public function validateConfiguration(string $category, string $key, $value): array
    {
        $errors = [];

        switch ($category) {
            case self::CATEGORY_PASSWORD:
                $errors = $this->validatePasswordConfig($key, $value);
                break;
            
            case self::CATEGORY_SESSION:
                $errors = $this->validateSessionConfig($key, $value);
                break;
            
            case self::CATEGORY_TWO_FACTOR:
                $errors = $this->validateTwoFactorConfig($key, $value);
                break;
            
            case self::CATEGORY_IP_RESTRICTION:
                $errors = $this->validateIpRestrictionConfig($key, $value);
                break;
            
            case self::CATEGORY_SECURITY_HEADERS:
                $errors = $this->validateSecurityHeadersConfig($key, $value);
                break;
            
            case self::CATEGORY_ENCRYPTION:
                $errors = $this->validateEncryptionConfig($key, $value);
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get security audit log
     */
    public function getSecurityAuditLog(array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM security_audit_log";
            $params = [];

            // Add filters
            if (!empty($filters['category'])) {
                $sql .= " WHERE category = ?";
                $params[] = $filters['category'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $logs = $this->db->fetchAll($sql, $params);
            
            foreach ($logs as &$log) {
                $log['old_value'] = json_decode($log['old_value'] ?? '{}', true) ?? [];
                $log['new_value'] = json_decode($log['new_value'] ?? '{}', true) ?? [];
            }
            
            return $logs;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get security audit log", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get security statistics
     */
    public function getSecurityStats(): array
    {
        try {
            $stats = [];

            // Configuration summary
            $configSummary = $this->db->fetchAll("
                SELECT category, COUNT(*) as count 
                FROM security_configurations 
                GROUP BY category
            ");
            
            $stats['configuration_summary'] = [];
            foreach ($configSummary as $summary) {
                $stats['configuration_summary'][$summary['category']] = $summary['count'];
            }

            // Recent changes
            $stats['recent_changes'] = $this->db->fetchAll("
                SELECT * FROM security_audit_log 
                ORDER BY created_at DESC 
                LIMIT 10
            ");

            // Policy compliance
            $stats['policy_compliance'] = $this->checkPolicyCompliance();

            // Security score
            $stats['security_score'] = $this->calculateSecurityScore();

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
            "CREATE TABLE IF NOT EXISTS security_configurations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category VARCHAR(50) NOT NULL,
                config_key VARCHAR(255) NOT NULL,
                config_value JSON,
                is_sensitive BOOLEAN DEFAULT FALSE,
                updated_by VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_config (category, config_key),
                INDEX idx_category (category),
                INDEX idx_updated_at (updated_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS security_audit_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category VARCHAR(50) NOT NULL,
                config_key VARCHAR(255) NOT NULL,
                old_value JSON,
                new_value JSON,
                updated_by VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_updated_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function loadDefaultPolicies(): void
    {
        $this->policies = [
            self::CATEGORY_PASSWORD => [
                'min_length' => $this->config['password_min_length'],
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_number' => true,
                'require_special_char' => true,
                'max_age_days' => 90,
                'history_count' => 5
            ],
            self::CATEGORY_SESSION => [
                'timeout' => $this->config['session_timeout'],
                'max_concurrent' => 3,
                'regenerate_interval' => 300,
                'secure_flags' => true,
                'httponly_flags' => true
            ],
            self::CATEGORY_TWO_FACTOR => [
                'enabled' => $this->config['two_factor_enabled'],
                'methods' => ['totp', 'email', 'sms'],
                'backup_codes' => 10,
                'code_length' => 6,
                'code_ttl' => 300
            ],
            self::CATEGORY_IP_RESTRICTION => [
                'enabled' => $this->config['ip_whitelist_enabled'],
                'whitelist_mode' => false,
                'blacklist_mode' => true,
                'max_attempts_per_ip' => 20,
                'ip_lockout_duration' => 3600
            ],
            self::CATEGORY_SECURITY_HEADERS => [
                'x_frame_options' => 'DENY',
                'x_xss_protection' => '1; mode=block',
                'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'",
                'strict_transport_security' => 'max-age=31536000; includeSubDomains',
                'referrer_policy' => 'strict-origin-when-cross-origin'
            ],
            self::CATEGORY_ENCRYPTION => [
                'algorithm' => 'AES-256-GCM',
                'key_rotation_days' => 90,
                'hash_algorithm' => 'SHA-256',
                'salt_length' => 32
            ],
            self::CATEGORY_AUDIT => [
                'log_level' => 'INFO',
                'log_retention_days' => 365,
                'alert_on_failures' => true,
                'alert_threshold' => 5
            ]
        ];
    }

    private function applySecurityConfiguration(string $category, string $key, $value): array
    {
        try {
            switch ($category) {
                case self::CATEGORY_SESSION:
                    if ($key === 'timeout') {
                        ini_set('session.gc_maxlifetime', $value);
                        ini_set('session.cookie_lifetime', $value);
                    }
                    if ($key === 'secure_flags') {
                        ini_set('session.cookie_secure', $value ? 1 : 0);
                        ini_set('session.use_only_cookies', 1);
                    }
                    if ($key === 'httponly_flags') {
                        ini_set('session.cookie_httponly', $value ? 1 : 0);
                    }
                    break;
                
                case self::CATEGORY_SECURITY_HEADERS:
                    // Security headers would be applied at the web server level
                    break;
            }

            return [
                'success' => true,
                'message' => 'Configuration applied successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to apply configuration: ' . $e->getMessage()
            ];
        }
    }

    private function validatePasswordConfig(string $key, $value): array
    {
        $errors = [];

        switch ($key) {
            case 'min_length':
                if (!is_int($value) || $value < 8 || $value > 128) {
                    $errors[] = 'Password minimum length must be between 8 and 128 characters';
                }
                break;
            
            case 'require_uppercase':
            case 'require_lowercase':
            case 'require_number':
            case 'require_special_char':
                if (!is_bool($value)) {
                    $errors[] = ucfirst($key) . ' must be a boolean value';
                }
                break;
            
            case 'max_age_days':
                if (!is_int($value) || $value < 1 || $value > 365) {
                    $errors[] = 'Password maximum age must be between 1 and 365 days';
                }
                break;
        }

        return $errors;
    }

    private function validateSessionConfig(string $key, $value): array
    {
        $errors = [];

        switch ($key) {
            case 'timeout':
                if (!is_int($value) || $value < 300 || $value > 86400) {
                    $errors[] = 'Session timeout must be between 5 minutes and 24 hours';
                }
                break;
            
            case 'max_concurrent':
                if (!is_int($value) || $value < 1 || $value > 10) {
                    $errors[] = 'Maximum concurrent sessions must be between 1 and 10';
                }
                break;
        }

        return $errors;
    }

    private function validateTwoFactorConfig(string $key, $value): array
    {
        $errors = [];

        switch ($key) {
            case 'enabled':
                if (!is_bool($value)) {
                    $errors[] = 'Two-factor authentication enabled must be a boolean value';
                }
                break;
            
            case 'methods':
                if (!is_array($value) || empty($value)) {
                    $errors[] = 'Two-factor methods must be a non-empty array';
                }
                break;
        }

        return $errors;
    }

    private function validateIpRestrictionConfig(string $key, $value): array
    {
        $errors = [];

        switch ($key) {
            case 'enabled':
                if (!is_bool($value)) {
                    $errors[] = 'IP restriction enabled must be a boolean value';
                }
                break;
            
            case 'whitelist_mode':
                if (!is_bool($value)) {
                    $errors[] = 'Whitelist mode must be a boolean value';
                }
                break;
        }

        return $errors;
    }

    private function validateSecurityHeadersConfig(string $key, $value): array
    {
        $errors = [];

        switch ($key) {
            case 'x_frame_options':
                if (!in_array($value, ['DENY', 'SAMEORIGIN', 'ALLOW-FROM'])) {
                    $errors[] = 'X-Frame-Options must be DENY, SAMEORIGIN, or ALLOW-FROM';
                }
                break;
        }

        return $errors;
    }

    private function validateEncryptionConfig(string $key, $value): array
    {
        $errors = [];

        switch ($key) {
            case 'algorithm':
                if (!in_array($value, ['AES-128-GCM', 'AES-192-GCM', 'AES-256-GCM'])) {
                    $errors[] = 'Encryption algorithm must be AES-128-GCM, AES-192-GCM, or AES-256-GCM';
                }
                break;
        }

        return $errors;
    }

    private function auditConfigurationChange(string $category, string $key, $oldValue, $newValue): void
    {
        $sql = "INSERT INTO security_audit_log 
                (category, config_key, old_value, new_value, updated_by, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $category,
            $key,
            json_encode($oldValue),
            json_encode($newValue),
            $this->getCurrentUserId(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    private function getCurrentUserId(): string
    {
        return $_SESSION['user_id'] ?? 'system';
    }

    private function checkPolicyCompliance(): array
    {
        $compliance = [];
        $configurations = $this->getConfiguration();

        // Check password policy compliance
        $passwordConfig = $configurations[self::CATEGORY_PASSWORD] ?? [];
        $compliance['password_strength'] = isset($passwordConfig['min_length']) && $passwordConfig['min_length'] >= 12;

        // Check session security
        $sessionConfig = $configurations[self::CATEGORY_SESSION] ?? [];
        $compliance['session_security'] = isset($sessionConfig['secure_flags']) && $sessionConfig['secure_flags'];

        // Check two-factor authentication
        $twoFactorConfig = $configurations[self::CATEGORY_TWO_FACTOR] ?? [];
        $compliance['two_factor_enabled'] = isset($twoFactorConfig['enabled']) && $twoFactorConfig['enabled'];

        return $compliance;
    }

    private function calculateSecurityScore(): int
    {
        $configurations = $this->getConfiguration();
        $score = 0;

        // Password security (30 points)
        $passwordConfig = $configurations[self::CATEGORY_PASSWORD] ?? [];
        if (isset($passwordConfig['min_length']) && $passwordConfig['min_length'] >= 12) $score += 10;
        if (isset($passwordConfig['require_uppercase']) && $passwordConfig['require_uppercase']) $score += 5;
        if (isset($passwordConfig['require_lowercase']) && $passwordConfig['require_lowercase']) $score += 5;
        if (isset($passwordConfig['require_number']) && $passwordConfig['require_number']) $score += 5;
        if (isset($passwordConfig['require_special_char']) && $passwordConfig['require_special_char']) $score += 5;

        // Session security (25 points)
        $sessionConfig = $configurations[self::CATEGORY_SESSION] ?? [];
        if (isset($sessionConfig['timeout']) && $sessionConfig['timeout'] <= 3600) $score += 10;
        if (isset($sessionConfig['secure_flags']) && $sessionConfig['secure_flags']) $score += 10;
        if (isset($sessionConfig['httponly_flags']) && $sessionConfig['httponly_flags']) $score += 5;

        // Two-factor authentication (25 points)
        $twoFactorConfig = $configurations[self::CATEGORY_TWO_FACTOR] ?? [];
        if (isset($twoFactorConfig['enabled']) && $twoFactorConfig['enabled']) $score += 25;

        // IP restrictions (20 points)
        $ipConfig = $configurations[self::CATEGORY_IP_RESTRICTION] ?? [];
        if (isset($ipConfig['enabled']) && $ipConfig['enabled']) $score += 20;

        return min(100, $score);
    }
}
