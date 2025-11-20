-- System Logs Table Migration
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `level` ENUM('INFO', 'WARNING', 'ERROR', 'SECURITY') NOT NULL DEFAULT 'INFO',
    `action` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `username` VARCHAR(100) NULL,
    `details` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index for performance
CREATE INDEX idx_system_logs_level ON `system_logs` (`level`);
CREATE INDEX idx_system_logs_username ON `system_logs` (`username`);
CREATE INDEX idx_system_logs_created_at ON `system_logs` (`created_at`);
