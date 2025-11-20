-- Security Monitoring and Threat Detection Tables Migration

-- Login Attempts Tracking Table
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `successful` BOOLEAN DEFAULT FALSE,
    `user_agent` VARCHAR(255) NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IP Block List Table
CREATE TABLE IF NOT EXISTS `ip_block_list` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL UNIQUE,
    `blocked_until` TIMESTAMP NULL,
    `block_reason` ENUM(
        'BRUTE_FORCE', 
        'SUSPICIOUS_ACTIVITY', 
        'MANUAL_BLOCK'
    ) NOT NULL,
    `block_count` INT UNSIGNED DEFAULT 1,
    `first_blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_blocked_status` (`ip_address`, `blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate Limiting Table
CREATE TABLE IF NOT EXISTS `rate_limit` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `identifier` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_identifier` (`identifier`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Alerts Table
CREATE TABLE IF NOT EXISTS `security_alerts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `alert_type` ENUM(
        'LOGIN_ATTEMPT',
        'SQL_INJECTION',
        'XSS_ATTEMPT',
        'RATE_LIMIT_EXCEEDED',
        'UNAUTHORIZED_ACCESS',
        'POTENTIAL_BREACH'
    ) NOT NULL,
    `severity` ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'MEDIUM',
    `description` TEXT,
    `user_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `resolved` BOOLEAN DEFAULT FALSE,
    `additional_data` JSON NULL,
    INDEX `idx_alert_type` (`alert_type`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_timestamp` (`timestamp`),
    FOREIGN KEY (`user_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Threat Intelligence Table
CREATE TABLE IF NOT EXISTS `threat_intelligence` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL UNIQUE,
    `threat_score` INT UNSIGNED DEFAULT 0,
    `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `country` VARCHAR(100) NULL,
    `is_tor_exit_node` BOOLEAN DEFAULT FALSE,
    `is_known_attacker` BOOLEAN DEFAULT FALSE,
    `reputation_score` DECIMAL(5,2) DEFAULT 50.00,
    INDEX `idx_threat_score` (`threat_score`),
    INDEX `idx_reputation_score` (`reputation_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for performance optimization
CREATE INDEX idx_login_attempts_time ON `login_attempts` (`attempt_time`);
CREATE INDEX idx_ip_block_status ON `ip_block_list` (`ip_address`, `blocked_until`);
CREATE INDEX idx_security_alerts_unresolved ON `security_alerts` (`resolved`, `severity`);
