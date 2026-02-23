<?php

/**
 * Migration: Create system monitoring tables
 * Adds tables for logging, metrics, and performance monitoring
 */

class CreateSystemMonitoringTables
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up()
    {
        // Create system_logs table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS system_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME NOT NULL,
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context JSON,
                file VARCHAR(255),
                line INT,
                request_id VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_timestamp (timestamp),
                INDEX idx_level (level),
                INDEX idx_request_id (request_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create system_metrics table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS system_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME NOT NULL,
                memory_current BIGINT UNSIGNED,
                memory_peak BIGINT UNSIGNED,
                cpu_load_1 DECIMAL(5,2),
                cpu_load_5 DECIMAL(5,2),
                cpu_load_15 DECIMAL(5,2),
                disk_total BIGINT UNSIGNED,
                disk_used BIGINT UNSIGNED,
                disk_free BIGINT UNSIGNED,
                server_uptime INT UNSIGNED,
                db_connections INT UNSIGNED,
                active_sessions INT UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_timestamp (timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create performance_logs table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS performance_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME NOT NULL,
                operation VARCHAR(255) NOT NULL,
                duration_ms DECIMAL(10,2),
                memory_usage BIGINT UNSIGNED,
                context JSON,
                request_id VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_operation (operation),
                INDEX idx_timestamp (timestamp),
                INDEX idx_request_id (request_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create user_activity_logs table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS user_activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                action VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                context JSON,
                timestamp DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_timestamp (timestamp),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Create error_logs table for detailed error tracking
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS error_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME NOT NULL,
                error_type VARCHAR(100),
                error_message TEXT,
                file VARCHAR(255),
                line INT,
                stack_trace TEXT,
                request_method VARCHAR(10),
                request_uri TEXT,
                user_id INT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                context JSON,
                resolved BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_error_type (error_type),
                INDEX idx_timestamp (timestamp),
                INDEX idx_user_id (user_id),
                INDEX idx_resolved (resolved)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create system_alerts table for automated alerts
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS system_alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_type VARCHAR(100) NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                message TEXT NOT NULL,
                context JSON,
                acknowledged BOOLEAN DEFAULT FALSE,
                acknowledged_by INT,
                acknowledged_at DATETIME,
                resolved BOOLEAN DEFAULT FALSE,
                resolved_at DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_alert_type (alert_type),
                INDEX idx_severity (severity),
                INDEX idx_acknowledged (acknowledged),
                INDEX idx_resolved (resolved)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "✅ System monitoring tables created successfully\n";
    }

    public function down()
    {
        $tables = [
            'system_alerts',
            'error_logs',
            'user_activity_logs',
            'performance_logs',
            'system_metrics',
            'system_logs'
        ];

        foreach ($tables as $table) {
            $this->db->exec("DROP TABLE IF EXISTS {$table}");
        }

        echo "✅ System monitoring tables dropped successfully\n";
    }
}
