<?php

namespace App\Services;

use App\Services\Security\SecurityService as ModernSecurityService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Compatibility wrapper for SecurityService
 */
class SecurityService extends ModernSecurityService
{
    public function __construct($db = null, $logger = null, array $securityConfig = [])
    {
        if (!($db instanceof Database)) {
            $db = Database::getInstance();
        }
        
        if ($logger === null) {
            // Create fallback logger
            $logger = new class implements LoggerInterface {
                public function emergency($message, array $context = []): void { error_log("EMERGENCY: " . $message); }
                public function alert($message, array $context = []): void { error_log("ALERT: " . $message); }
                public function critical($message, array $context = []): void { error_log("CRITICAL: " . $message); }
                public function error($message, array $context = []): void { error_log("ERROR: " . $message); }
                public function warning($message, array $context = []): void { error_log("WARNING: " . $message); }
                public function notice($message, array $context = []): void { error_log("NOTICE: " . $message); }
                public function info($message, array $context = []): void { error_log("INFO: " . $message); }
                public function debug($message, array $context = []): void { error_log("DEBUG: " . $message); }
                public function log($level, $message, array $context = []): void { error_log(strtoupper($level) . ": " . $message); }
            };
        }
        
        parent::__construct($db, $logger, $securityConfig);
    }

    public function listBlocked(int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM blocked_ips ORDER BY created_at DESC LIMIT " . intval($limit);
            return $this->db->fetchAll($sql) ?: [];
        } catch (\Exception $e) {
            error_log("Failed to list blocked IPs: " . $e->getMessage());
            return [];
        }
    }

    public function getFailedAttempts(string $email = '', int $hours = 24): array
    {
        try {
            $sql = "SELECT email, ip_address, reason, attempt_at FROM failed_login_attempts 
                    WHERE attempt_at > DATE_SUB(NOW(), INTERVAL ? HOUR)";
            $params = [$hours];
            if ($email) {
                $sql .= " AND email = ?";
                $params[] = $email;
            }
            $sql .= " ORDER BY attempt_at DESC LIMIT 100";
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (\Exception $e) {
            error_log("Failed to get failed login attempts: " . $e->getMessage());
            return [];
        }
    }
}
