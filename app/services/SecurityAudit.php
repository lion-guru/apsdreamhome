<?php

namespace App\Services;

use App\Core\Database;

class SecurityAudit
{
    protected $db;
    protected static $instance = null;

    private function __construct()
    {
        $this->db = \App\Core\App::database();
        $this->ensureAuditTableExists();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log a security event
     *
     * @param string $event Event type (e.g., 'login_failure', 'sql_injection_attempt')
     * @param string $description Detailed description
     * @param string|null $userId User ID if applicable
     * @param string $severity 'low', 'medium', 'high', 'critical'
     */
    public function log(string $event, string $description, ?string $userId = null, string $severity = 'medium'): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';

        $this->db->execute(
            "INSERT INTO security_audit_logs (event_type, description, user_id, ip_address, user_agent, request_uri, severity, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$event, $description, $userId, $ip, $userAgent, $requestUri, $severity]
        );
    }

    /**
     * Ensure the audit table exists
     */
    protected function ensureAuditTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS security_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            description TEXT,
            user_id VARCHAR(50),
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            request_uri VARCHAR(255),
            severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            created_at DATETIME
        )";
        $this->db->query($sql);
    }
}
