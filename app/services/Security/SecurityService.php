<?php

namespace App\Services\Security;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Security Service
 * Handles comprehensive security operations with proper MVC patterns
 */
class SecurityService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $securityConfig;
    private string $logFile;

    // Security Levels
    public const LEVEL_LOW = 1;
    public const LEVEL_MEDIUM = 2;
    public const LEVEL_HIGH = 3;
    public const LEVEL_CRITICAL = 4;

    // Threat Types
    public const THREAT_SQL_INJECTION = 'sql_injection';
    public const THREAT_XSS = 'xss';
    public const THREAT_CSRF = 'csrf';
    public const THREAT_BRUTE_FORCE = 'brute_force';
    public const THREAT_PATH_TRAVERSAL = 'path_traversal';
    public const THREAT_FILE_UPLOAD = 'file_upload';

    public function __construct(Database $db, LoggerInterface $logger, array $securityConfig = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->securityConfig = array_merge([
            'max_login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'session_timeout' => 3600, // 1 hour
            'password_min_length' => 8,
            'log_file' => __DIR__ . '/../../../../logs/security.log'
        ], $securityConfig);
        
        $this->logFile = $this->securityConfig['log_file'];
        $this->initializeSecurity();
    }

    /**
     * Input sanitization
     */
    public function sanitize(string $input, string $type = 'general'): string
    {
        try {
            switch ($type) {
                case 'email':
                    return filter_var($input, FILTER_SANITIZE_EMAIL);
                case 'url':
                    return filter_var($input, FILTER_SANITIZE_URL);
                case 'int':
                    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                case 'float':
                    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                case 'html':
                    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                case 'sql':
                    return $this->sanitizeSql($input);
                case 'filename':
                    return $this->sanitizeFilename($input);
                case 'general':
                default:
                    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            }
        } catch (\Exception $e) {
            $this->logger->error("Sanitization failed", ['input' => $input, 'type' => $type, 'error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Validate input
     */
    public function validate(string $input, array $rules): array
    {
        $result = [
            'valid' => true,
            'errors' => []
        ];

        foreach ($rules as $rule => $params) {
            switch ($rule) {
                case 'required':
                    if (empty($input)) {
                        $result['valid'] = false;
                        $result['errors'][] = $params['message'] ?? 'This field is required';
                    }
                    break;

                case 'min_length':
                    if (strlen($input) < $params['value']) {
                        $result['valid'] = false;
                        $result['errors'][] = $params['message'] ?? "Minimum length is {$params['value']} characters";
                    }
                    break;

                case 'max_length':
                    if (strlen($input) > $params['value']) {
                        $result['valid'] = false;
                        $result['errors'][] = $params['message'] ?? "Maximum length is {$params['value']} characters";
                    }
                    break;

                case 'email':
                    if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                        $result['valid'] = false;
                        $result['errors'][] = $params['message'] ?? 'Invalid email format';
                    }
                    break;

                case 'regex':
                    if (!preg_match($params['pattern'], $input)) {
                        $result['valid'] = false;
                        $result['errors'][] = $params['message'] ?? 'Invalid format';
                    }
                    break;

                case 'numeric':
                    if (!is_numeric($input)) {
                        $result['valid'] = false;
                        $result['errors'][] = $params['message'] ?? 'Must be numeric';
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * CSRF token management
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Check if token is expired (1 hour)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Rate limiting
     */
    public function checkRateLimit(string $identifier, int $maxAttempts = 5, int $windowSeconds = 900): bool
    {
        try {
            $sql = "SELECT COUNT(*) as attempts FROM rate_limits 
                    WHERE identifier = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)";
            
            $attempts = $this->db->fetchOne($sql, [$identifier, $windowSeconds]) ?? 0;
            
            if ($attempts >= $maxAttempts) {
                $this->logSecurityEvent('rate_limit_exceeded', [
                    'identifier' => $identifier,
                    'attempts' => $attempts,
                    'max_attempts' => $maxAttempts
                ], self::LEVEL_HIGH);
                
                return false;
            }

            // Record this attempt
            $this->db->execute("INSERT INTO rate_limits (identifier, created_at) VALUES (?, NOW())", [$identifier]);
            
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Rate limit check failed", ['identifier' => $identifier, 'error' => $e->getMessage()]);
            return true; // Allow on error
        }
    }

    /**
     * Password strength validation
     */
    public function validatePasswordStrength(string $password): array
    {
        $strength = 0;
        $feedback = [];

        // Length check
        if (strlen($password) >= 8) {
            $strength += 20;
        } else {
            $feedback[] = 'Password should be at least 8 characters long';
        }

        // Uppercase check
        if (preg_match('/[A-Z]/', $password)) {
            $strength += 20;
        } else {
            $feedback[] = 'Include at least one uppercase letter';
        }

        // Lowercase check
        if (preg_match('/[a-z]/', $password)) {
            $strength += 20;
        } else {
            $feedback[] = 'Include at least one lowercase letter';
        }

        // Number check
        if (preg_match('/[0-9]/', $password)) {
            $strength += 20;
        } else {
            $feedback[] = 'Include at least one number';
        }

        // Special character check
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $strength += 20;
        } else {
            $feedback[] = 'Include at least one special character';
        }

        return [
            'strength' => $strength,
            'level' => $this->getPasswordLevel($strength),
            'feedback' => $feedback
        ];
    }

    /**
     * File upload security
     */
    public function validateFileUpload(array $file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'sanitized_name' => null
        ];

        // Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            $result['valid'] = false;
            $result['errors'][] = 'Invalid file upload';
            return $result;
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            $result['valid'] = false;
            $result['errors'][] = 'File size exceeds maximum limit';
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            $result['valid'] = false;
            $result['errors'][] = 'File type not allowed';
        }

        // Sanitize filename
        $result['sanitized_name'] = $this->sanitizeFilename($file['name']);

        return $result;
    }

    /**
     * SQL injection detection
     */
    public function detectSqlInjection(string $input): bool
    {
        $sqlPatterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)(\s|$)/i',
            '/(\s|^)(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/(\s|^)(OR|AND)\s+\w+\s*=\s*\w+/i',
            '/(\s|^)(--|#|\/\*|\*\/)/i',
            '/(\s|^)(WAITFOR|DELAY)/i',
            '/(\s|^)(BENCHMARK|SLEEP)/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logSecurityEvent('sql_injection_attempt', [
                    'input' => $input,
                    'pattern' => $pattern
                ], self::LEVEL_CRITICAL);
                
                return true;
            }
        }

        return false;
    }

    /**
     * XSS detection
     */
    public function detectXss(string $input): bool
    {
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
            '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i'
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logSecurityEvent('xss_attempt', [
                    'input' => $input,
                    'pattern' => $pattern
                ], self::LEVEL_HIGH);
                
                return true;
            }
        }

        return false;
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $eventType, array $data = [], int $level = self::LEVEL_MEDIUM): void
    {
        try {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'event_type' => $eventType,
                'level' => $level,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'user_id' => $_SESSION['user_id'] ?? null,
                'data' => $data
            ];

            // Log to file
            $logMessage = json_encode($logEntry) . "\n";
            file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);

            // Log to database
            $sql = "INSERT INTO security_logs (event_type, level, ip_address, user_agent, user_id, event_data, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $eventType,
                $level,
                $logEntry['ip_address'],
                $logEntry['user_agent'],
                $logEntry['user_id'],
                json_encode($data)
            ]);

            $this->logger->warning("Security event logged", $logEntry);

        } catch (\Exception $e) {
            $this->logger->error("Failed to log security event", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get security statistics
     */
    public function getSecurityStats(): array
    {
        try {
            $stats = [];

            // Events today
            $stats['events_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM security_logs WHERE DATE(created_at) = CURDATE()"
            ) ?? 0;

            // Events by type
            $typeStats = $this->db->fetchAll(
                "SELECT event_type, COUNT(*) as count FROM security_logs 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 GROUP BY event_type"
            );

            $stats['by_type'] = [];
            foreach ($typeStats as $stat) {
                $stats['by_type'][$stat['event_type']] = $stat['count'];
            }

            // Events by level
            $levelStats = $this->db->fetchAll(
                "SELECT level, COUNT(*) as count FROM security_logs 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 GROUP BY level"
            );

            $stats['by_level'] = [];
            foreach ($levelStats as $stat) {
                $stats['by_level'][$stat['level']] = $stat['count'];
            }

            // Recent threats
            $stats['recent_threats'] = $this->db->fetchAll(
                "SELECT * FROM security_logs 
                 WHERE level >= 3 
                 ORDER BY created_at DESC 
                 LIMIT 10"
            );

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get security stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Initialize security system
     */
    private function initializeSecurity(): void
    {
        try {
            $this->createSecurityTables();
            $this->ensureLogDirectory();
        } catch (\Exception $e) {
            $this->logger->error("Failed to initialize security", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create security tables
     */
    private function createSecurityTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS security_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                level INT NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                user_id INT,
                event_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_level (level),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_identifier (identifier),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Helper methods
     */
    private function sanitizeSql(string $input): string
    {
        // Remove SQL keywords and patterns
        $patterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b/i',
            '/\b(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/\b(OR|AND)\s+\w+\s*=\s*\w+/i',
            '/(--|#|\/\*|\*\/)/',
            '/\b(WAITFOR|DELAY|BENCHMARK|SLEEP)\b/i'
        ];

        foreach ($patterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        return trim($input);
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove directory traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Ensure it's not empty after sanitization
        if (empty($filename)) {
            $filename = 'file_' . time();
        }

        return $filename;
    }

    private function getPasswordLevel(int $strength): string
    {
        if ($strength < 40) return 'Weak';
        if ($strength < 60) return 'Fair';
        if ($strength < 80) return 'Good';
        return 'Strong';
    }
}
