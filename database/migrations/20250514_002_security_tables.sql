-- Security-related Database Tables Migration

-- IP Blacklist Table
CREATE TABLE IF NOT EXISTS ip_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    blocked_until DATETIME NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ip (ip_address),
    INDEX idx_blocked_until (blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate Limiting Table
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    timestamp INT UNSIGNED NOT NULL,
    
    INDEX idx_identifier (identifier),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Audit Log Table
CREATE TABLE IF NOT EXISTS security_audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM(
        'login_attempt', 
        'login_success', 
        'login_failure', 
        'password_reset', 
        'account_lockout', 
        'suspicious_activity',
        'permission_change',
        'data_access'
    ) NOT NULL,
    user_id INT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    details TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'low',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Two-Factor Authentication Tokens
CREATE TABLE IF NOT EXISTS two_factor_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    method ENUM('email', 'sms', 'authenticator_app') NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sensitive Data Encryption Keys Management
CREATE TABLE IF NOT EXISTS encryption_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purpose VARCHAR(50) NOT NULL UNIQUE,
    key_hash VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rotated_at TIMESTAMP NULL,
    active BOOLEAN DEFAULT TRUE,
    
    INDEX idx_purpose (purpose),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial encryption key record (placeholder)
INSERT IGNORE INTO encryption_keys 
(purpose, key_hash, rotated_at) 
VALUES 
('app_primary_encryption', SHA2(UUID(), 256), NOW());

-- Optimize tables for performance
OPTIMIZE TABLE 
    ip_blacklist, 
    rate_limits, 
    security_audit_logs, 
    two_factor_tokens, 
    encryption_keys;
