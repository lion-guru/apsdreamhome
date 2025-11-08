<?php
namespace Database\Migrations;

use Database\DatabaseConnection;
use Exception;

class LoggingMigration {
    private $db;
    private $logger;

    public function __construct() {
        // Initialize database connection
        $this->db = DatabaseConnection::getInstance();
        
        // Initialize logging (if available)
        $this->logger = new SystemLogger();
    }

    /**
     * Execute advanced logging system migration
     */
    public function migrate() {
        try {
            // Begin transaction for atomic migration
            $this->db->beginTransaction();

            // Create Comprehensive Audit Log Table
            $this->createComprehensiveAuditLogTable();

            // Create Security Event Log Table
            $this->createSecurityEventLogTable();

            // Create System Performance Log Table
            $this->createSystemPerformanceLogTable();

            // Create API Request Log Table
            $this->createAPIRequestLogTable();

            // Create Log Cleanup Stored Procedure
            $this->createLogCleanupProcedure();

            // Create Log Cleanup Event
            $this->createLogCleanupEvent();

            // Commit transaction
            $this->db->commit();

            // Log successful migration
            $this->logger->info('Advanced Logging System Migration Completed Successfully', [
                'migration_type' => 'logging_system',
                'tables_created' => [
                    'comprehensive_audit_log',
                    'security_event_log',
                    'system_performance_log',
                    'api_request_log'
                ]
            ]);

            return true;
        } catch (Exception $e) {
            // Rollback transaction on failure
            $this->db->rollBack();

            // Log migration error
            $this->logger->error('Logging System Migration Failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Create Comprehensive Audit Log Table
     */
    private function createComprehensiveAuditLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `comprehensive_audit_log` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `trace_id` VARCHAR(36) NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `username` VARCHAR(100) NULL,
            `action_type` ENUM('login', 'logout', 'create', 'update', 'delete', 'access', 'security', 'system') NOT NULL,
            `resource_type` VARCHAR(100) NULL,
            `resource_id` VARCHAR(50) NULL,
            `severity_level` ENUM('info', 'warning', 'error', 'critical', 'emergency') NOT NULL DEFAULT 'info',
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `request_method` VARCHAR(10) NULL,
            `request_uri` TEXT NULL,
            `request_payload` LONGTEXT NULL,
            `response_status` INT UNSIGNED NULL,
            `execution_time` DECIMAL(10,4) NULL,
            `additional_context` JSON NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `created_by` VARCHAR(100) NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_trace_id` (`trace_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_action_type` (`action_type`),
            INDEX `idx_severity_level` (`severity_level`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Create Security Event Log Table
     */
    private function createSecurityEventLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `security_event_log` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `event_type` ENUM('login_attempt', 'password_change', 'role_change', 'access_denied', 'suspicious_activity') NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `username` VARCHAR(100) NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `geo_location` JSON NULL,
            `device_info` JSON NULL,
            `threat_score` DECIMAL(5,2) NOT NULL DEFAULT 0,
            `is_blocked` BOOLEAN NOT NULL DEFAULT FALSE,
            `details` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_event_type` (`event_type`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_ip_address` (`ip_address`),
            INDEX `idx_threat_score` (`threat_score`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Create System Performance Log Table
     */
    private function createSystemPerformanceLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `system_performance_log` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `cpu_usage` DECIMAL(5,2) NULL,
            `memory_usage` DECIMAL(5,2) NULL,
            `disk_usage` DECIMAL(5,2) NULL,
            `network_traffic_in` BIGINT UNSIGNED NULL,
            `network_traffic_out` BIGINT UNSIGNED NULL,
            `active_connections` INT UNSIGNED NULL,
            `server_load` DECIMAL(5,2) NULL,
            `additional_metrics` JSON NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_timestamp` (`timestamp`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Create API Request Log Table
     */
    private function createAPIRequestLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `api_request_log` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `api_key` VARCHAR(100) NULL,
            `endpoint` VARCHAR(255) NOT NULL,
            `method` VARCHAR(10) NOT NULL,
            `request_payload` LONGTEXT NULL,
            `response_payload` LONGTEXT NULL,
            `response_status` INT UNSIGNED NOT NULL,
            `execution_time` DECIMAL(10,4) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_api_key` (`api_key`),
            INDEX `idx_endpoint` (`endpoint`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Create Log Cleanup Stored Procedure
     */
    private function createLogCleanupProcedure() {
        $sql = "DROP PROCEDURE IF EXISTS `cleanup_old_logs`;
        CREATE PROCEDURE `cleanup_old_logs`()
        BEGIN
            DELETE FROM `comprehensive_audit_log` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 90 DAY);
            DELETE FROM `security_event_log` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 90 DAY);
            DELETE FROM `system_performance_log` WHERE `timestamp` < DATE_SUB(NOW(), INTERVAL 30 DAY);
            DELETE FROM `api_request_log` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 60 DAY);
        END";

        $this->db->exec($sql);
    }

    /**
     * Create Log Cleanup Event
     */
    private function createLogCleanupEvent() {
        // Enable event scheduler
        $this->db->exec("SET GLOBAL event_scheduler = ON");

        // Drop existing event if it exists
        $this->db->exec("DROP EVENT IF EXISTS `daily_log_cleanup`");

        // Create new event
        $sql = "CREATE EVENT `daily_log_cleanup`
                ON SCHEDULE EVERY 1 DAY
                DO CALL `cleanup_old_logs`()";

        $this->db->exec($sql);
    }

    /**
     * Rollback migration if something goes wrong
     */
    public function rollback() {
        try {
            $tables = [
                'comprehensive_audit_log',
                'security_event_log',
                'system_performance_log',
                'api_request_log'
            ];

            foreach ($tables as $table) {
                $this->db->exec("DROP TABLE IF EXISTS `$table`");
            }

            $this->db->exec("DROP PROCEDURE IF EXISTS `cleanup_old_logs`");
            $this->db->exec("DROP EVENT IF EXISTS `daily_log_cleanup`");

            $this->logger->info('Logging System Migration Rolled Back', [
                'migration_type' => 'logging_system_rollback'
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Logging System Rollback Failed', [
                'error_message' => $e->getMessage()
            ]);

            return false;
        }
    }
}
